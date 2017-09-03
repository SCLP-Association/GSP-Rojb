<?php
Flight::route("POST|PUT /kansi/petty_cash/topup", function() use ($g, $pdo, $params) {
    requiredFields($params, "no_bukti, tgl, nilai, token");

    $params->no_bukti = trim(strtoupper($params->no_bukti));
    if (!isRowExist("petty_cash", "no_bukti", $params->no_bukti))
    {
        $pdo->con->beginTransaction();
        // insert topup detail
        $data = [
            "no_bukti" => [$params->no_bukti, "string"],
            "tgl" => [$params->tgl, "string"],
            "nilai" => [$params->nilai, "int"],
            "arus_kas" => ["MASUK", "string"],
            "tipe" => ["TOPUP", "string"],
            "created_by" => [$g->logged->username, "string"]
        ];
        $pdo->insert("petty_cash", $data);

        // update saldo petty cash
        $sql = "SELECT * FROM petty_cash_saldo LIMIT 1";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->execute();

        $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$row) 
        {
            // insert new row with initialize topup
            $data = [
                "saldo" => [$params->nilai, "int"]
            ];
            $pdo->insert("petty_cash_saldo", $data);
        }
        else 
        {
            // update petty cash saldo
            $sql = "UPDATE petty_cash_saldo SET saldo = saldo + :topup";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":topup", $params->nilai, PDO::PARAM_INT);
            $pdo->execute();
        }

        if ($pdo->sth->rowCount() > 0)
            setResponseStatus(true, "Topup petty cash berhasil!");
        else
            setResponseStatus(true, "Topup petty cash gagal!");

        $pdo->con->commit();
    }
    else
        setResponseStatus(false, "No bukti topup sudah ada sebelumnya!");
});