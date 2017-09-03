<?php

function dec_enc($action, $string) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $secret_iv = 'This is my secret iv';

    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == 'decrypt' ){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function encrypt($text)
{
    $salt = "Ge4Sp$$1@!aKuN3@";

    return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt,
        $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(
        MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}

function decrypt($text)
{
    $salt = "Ge4Sp$$1@!aKuN3@";

    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text),
        MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256,
        MCRYPT_MODE_ECB), MCRYPT_RAND)));
}

function isValidAutonumberType($type)
{
    if ($type == "AKTIVASI-SO-JASA" || 
        $type == "AKTIVASI-SO-BIAYA-LAIN" || 
        $type == "AKTIVASI-SO-PKB" || 
        $type == "AKTIVASI-SO-BA-WASPANG" || 
        $type == "AKTIVASI-RUTIN" || 
        $type == "SERPO-RUTIN" || 
        $type == "SERPO-PEMBELIAN-MATERIAL" || 
        $type == "PM-PENGGUNAAN-MATERIAL" || 
        $type == "QOUT-PENGELUARAN-MATERIAL" || 
        $type == "KUITANSI-SERPO-RUTIN" || 
        $type == "KUITANSI-AKTIVASI-RUTIN" || 
        $type == "KUITANSI-AKTIVASI-SO-JASA" || 
        $type == "KUITANSI-AKTIVASI-SO-BIAYA-LAIN" || 
        $type == "KUITANSI-SERPO-PEMBELIAN-MATERIAL" || 
        $type == "REFERENSI-JASA" || 
        $type == "REFERENSI-BIAYA" || 
        $type == "REFERENSI-POP" || 
        $type == "REFERENSI-MATERIAL" || 
        $type == "REFERENSI-KHS-JASA" || 
        $type == "REFERENSI-KHS-MATERIAL" || 
        $type == "TOPUP-PETTY-CASH" || 
        $type == "PEMBELIAN-MATERIAL-PUSAT" || 
        $type == "IH-TRANSFER" || 
        $type == "QTY-OUT" || 
        $type == "BA-RETUR" || 
        $type == "BA-KERUGIAN")
        return true;
    else
        return false;
}

function isValidDate($date)
{
    $d = DateTime::createFromFormat("Y-m-d", $date);
    return $d && $d->format("Y-m-d") === $date;
}

function getSaldoPettyCash()
{
    global $g;
    $sql = "SELECT saldo FROM petty_cash_saldo LIMIT 1";

    $g->pdo->sth = $g->pdo->con->prepare($sql);
    $g->pdo->execute();

    $row = $g->pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($row)
        return (int) $row->saldo;
    else
        return 0;
}

function isReferesiExist($kode, $type = "BIAYA")
{
    // this function will be unused
    $table = "referensi_biaya";
    switch ($type)
    {
        case "ITEM": $table = "master_item_pkb"; break;
        case "MATERIAL": $table = "master_material"; break;
    }

    $pdoBiaya = new MyPdo();
    $sql = "SELECT * FROM " . $table . " WHERE kode = :kode";
    $pdoBiaya->sth = $pdoBiaya->con->prepare($sql);
    $pdoBiaya->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdoBiaya->execute();

    $isReferesiExist = $pdoBiaya->sth->fetch(PDO::FETCH_OBJ);
    if (!$isReferesiExist)
        return false;
    
    return true;
}

function updateAutonumber($type, $g, $pdo, $requireDate = false)
{
    $type = trim(strtoupper($type));

    if ($requireDate)
    {
        $month = date("m");
        $year = date("y");
    }
    else
    {
        $month = "0";
        $year = "0";
    }

    if (isValidAutonumberType($type))
    {
        if (!$requireDate)
        {
            $sql = "SELECT * FROM auto_number WHERE number_type = :tipe ORDER BY created_at DESC LIMIT 1";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":tipe", $type, PDO::PARAM_STR);
            $pdo->execute();

            $lastNoBukti = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if ($lastNoBukti != NULL)
            {
                $sql = "UPDATE auto_number SET counter = counter + 1, 
                    modified_at = NOW(), modified_by = '" . $g->logged->username . "'   
                    WHERE number_type = :tipe";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":tipe", $type, PDO::PARAM_STR);
                $pdo->execute();
            }
            else
            {
                $data = [
                    "counter" => [1, "string"],
                    "month" => [0, "string"],
                    "year" => [0, "string"],
                    "number_type" => [$type, "string"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("auto_number", $data);
            }
        }
        else
        {
            $sql = "SELECT * FROM auto_number 
                WHERE month = '" . $month . "' AND year = '" . $year . "' AND number_type = :tipe";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":tipe", $type, PDO::PARAM_STR);
            $pdo->execute();

            $lastNoBukti = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if ($lastNoBukti != NULL)
            {
                $sql = "UPDATE auto_number SET counter = counter + 1, 
                    modified_at = NOW(), modified_by = '" . $g->logged->username . "'   
                    WHERE month = '" . $month . "' AND year = '" . $year . "' AND number_type = :tipe";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":tipe", $type, PDO::PARAM_STR);
                $pdo->execute();
            }
            else
            {
                $data = [
                    "counter" => [1, "string"],
                    "month" => [$month, "string"],
                    "year" => [$year, "string"],
                    "number_type" => [$type, "string"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("auto_number", $data);
            }
        }

        setResponseStatus(true, "Autonumber updated!");
    }
}

function updateAutonumberKuitansi($type, $g, $pdo, $params)
{
    $type = trim(strtoupper($type));

    if (isValidAutonumberType($type))
    {
        $sql = "SELECT * FROM auto_number_kuitansi WHERE type = :type AND no_bukti = :no_bukti";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":type", $type, PDO::PARAM_STR);
        $pdo->sth->bindParam(":no_bukti", $params->no_bukti, PDO::PARAM_STR);
        $pdo->execute();

        $lastNoBukti = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if ($lastNoBukti != NULL)
        {
            $sql = "UPDATE auto_number_kuitansi SET counter = counter + 1, 
                modified_at = NOW(), modified_by = '" . $g->logged->username . "'   
                WHERE no_bukti = :no_bukti";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":no_bukti", $params->no_bukti, PDO::PARAM_STR);
            $pdo->execute();
        }
        else
        {
            $data = [
                "counter" => [1, "string"],
                "no_bukti" => [$params->no_bukti, "string"],
                "type" => [$type, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("auto_number_kuitansi", $data);
        }

        setResponseStatus(true, "Autonumber kuitansi updated!");
    }
}

function requiredFields($params, $fields)
{
    $arrField = explode(",", $fields);

    foreach ($arrField as $key => $par)
    {
        $par = trim($par);

        if ($params->{$par} == "" || strlen($params->{$par}) == 0)
        {
            setResponseStatus(false, $par . " tidak boleh kosong!");
            setExitResponse();
            Flight::stop();
        }
    }
}

function isRowExist($table, $field, $value)
{
    global $g;
    $sql = "SELECT * FROM " . $table . " WHERE " . $field . " = :value";
    $g->pdo->sth = $g->pdo->con->prepare($sql);
    $g->pdo->sth->bindParam(':value', $value, PDO::PARAM_STR);
    $g->pdo->execute();

    $row = $g->pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$row)
        return false;

    return true;
}

function setResponseStatus($success = false, $message = "Requested URL not found!")
{
    global $g;
    $g->response["success"] = $success;
    $g->response["message"] = $message;
}

function setExitResponse()
{
    global $g;
    $params = json_encode($g->params);
    $respAudit = json_encode($g->response);

    $sql = "INSERT INTO audit_trails(audit_type, params, response, message, created_by)
            VALUES(:auditType, :params, :response, :message, 'SYSTEM')";

    $g->pdo->sth = $g->pdo->con->prepare($sql);
    $g->pdo->sth->bindParam(':auditType', $g->url, PDO::PARAM_STR);
    $g->pdo->sth->bindParam(':params', $params, PDO::PARAM_STR);
    $g->pdo->sth->bindParam(':response', $respAudit, PDO::PARAM_STR);
    $g->pdo->sth->bindParam(':message', $g->response["message"], PDO::PARAM_STR);
    $g->pdo->execute();

    $id = $g->pdo->con->lastInsertId();

    $date = new DateTime("NOW");
    $success = $g->response["success"] ? "TRUE" : "FALSE";
    file_put_contents("log-".$date->format("Y-m-d").".txt", 
        "[" . $date->format("Y-m-d H:i:s") . "] " . $success . ": " . $g->response["message"] . ", Audit Trails Id => " . $id . "\n", 
        FILE_APPEND);

    echo json_encode($g->response);
}