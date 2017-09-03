<?php
// AUTO NUMBER NO BUKTI
Flight::route("POST|PUT /util/autonumber/create", function() use ($g, $pdo, $params) {
    
    $params->type = trim(strtoupper($params->type));
    $day = date("d");
    $month = date("m");
    $year = date("y");

    if (isValidAutonumberType($params->type))
    {
        $g->response["result"]["type"] = $params->type;

        $sql = "SELECT * FROM auto_number 
            WHERE month = '" . $month . "' AND year = '" . $year . "' AND number_type = :tipe";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":tipe", $params->type, PDO::PARAM_STR);
        $pdo->execute();

        $lastNoBukti = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$lastNoBukti)
        {
            switch ($params->type)
            {
                case "QTY-OUT":
                    $g->response["result"]["auto_number"] = "QTY-OUT/001/" . $day . $month . $year;
                    break;

                case "RETUR":
                    $g->response["result"]["auto_number"] = "RETUR/001/" . $day . $month . $year;
                    break;
            }
        }
        else
        {
            $counter = (int) $lastNoBukti->counter + 1;
            switch ($params->type)
            {
                case "QTY-OUT":
                    $loop = 3 - strlen($counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter = "0" . $counter; 

                    $g->response["result"]["auto_number"] = "QTY-OUT/" . $counter . "/" . $day . $month . $year;
                    break;

                case "RETUR":
                    $loop = 3 - strlen($counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter = "0" . $counter; 

                    $g->response["result"]["auto_number"] = "RETUR/" . $counter . "/" . $day . $month . $year;
                    break;
            }
        }

        $counter = "";
        // autonumber yang tidak dipengaruhi oleh tgl -> untuk referensi
        switch ($params->type)
        {
            case "AKTIVASI-RUTIN":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'AKTIVASI-RUTIN' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "AKTIVASI/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "AKTIVASI/" . $counter . "/" . $month . $year;
                }
                break;

            case "AKTIVASI-SO-JASA":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'AKTIVASI-SO-JASA' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "SOJ/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "SOJ/" . $counter . "/" . $month . $year;
                }
                break;

            case "AKTIVASI-SO-BIAYA-LAIN":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'AKTIVASI-SO-BIAYA-LAIN' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "SOBL/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "SOBL/" . $counter . "/" . $month . $year;
                }
                break;

            case "AKTIVASI-SO-PKB":
                if (!isset($params->no_so))
                {
                    setResponseStatus(false, "No so required!");
                    setExitResponse();
                    Flight::stop();
                }
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'AKTIVASI-SO-PKB' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "PKB-00001" . "/SO" . $params->no_so;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "PKB-" . $counter . "/SO" . $params->no_so;
                }
                break;

            case "AKTIVASI-SO-BA-WASPANG":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'AKTIVASI-SO-BA-WASPANG' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "BA-WASPANG/00001";
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "BA-WASPANG/" . $counter;
                }
                break;

            case "SERPO-RUTIN":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'SERPO-RUTIN' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "SERPO/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "SERPO/" . $counter . "/" . $month . $year;
                }
                break;

            case "SERPO-PEMBELIAN-MATERIAL":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'SERPO-PEMBELIAN-MATERIAL' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "SERPO-MATERIAL/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "SERPO-MATERIAL/" . $counter . "/" . $month . $year;
                }
                break;
            
            case "PM-PENGGUNAAN-MATERIAL":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'PM-PENGGUNAAN-MATERIAL' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "PM-MATERIAL/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "PM-MATERIAL/" . $counter . "/" . $month . $year;
                }
                break;

            case "QOUT-PENGELUARAN-MATERIAL":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'QOUT-PENGELUARAN-MATERIAL' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "BA-QOUT/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "BA-QOUT/" . $counter . "/" . $month . $year;
                }
                break;

            case "BA-RETUR":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'BA-RETUR' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "BA-RETUR/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "BA-RETUR/" . $counter . "/" . $month . $year;
                }
                break;

            case "BA-KERUGIAN":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'BA-KERUGIAN' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "BA-KERUGIAN/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "BA-KERUGIAN/" . $counter . "/" . $month . $year;
                }
                break;
            
            case "REFERENSI-JASA":
                $sql = "SELECT kode FROM referensi_jasa ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $jasa = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$jasa)
                    $g->response["result"]["auto_number"] = "J001";
                else
                {
                    $counter = (int) substr($jasa->kode, 1, 3) + 1;
                    if ($counter > 9 && $counter < 100)
                        $g->response["result"]["auto_number"] = "J0" . $counter;
                    else if ($counter < 10)
                        $g->response["result"]["auto_number"] = "J00" . $counter;
                    else
                        $g->response["result"]["auto_number"] = "J" . $counter;
                } 
                break;

            case "REFERENSI-MATERIAL":
                $sql = "SELECT kode FROM referensi_material ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $jasa = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$jasa)
                    $g->response["result"]["auto_number"] = "M001";
                else
                {
                    $counter = (int) substr($jasa->kode, 1, 3) + 1;
                    if ($counter > 9 && $counter < 100)
                        $g->response["result"]["auto_number"] = "M0" . $counter;
                    else if ($counter < 10)
                        $g->response["result"]["auto_number"] = "M00" . $counter;
                    else
                        $g->response["result"]["auto_number"] = "M" . $counter;
                } 
                break;

            case "REFERENSI-KHS-JASA":
                $sql = "SELECT kode FROM referensi_khs WHERE kode LIKE 'JKHS%' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $jasa = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$jasa)
                    $g->response["result"]["auto_number"] = "JKHS001";
                else
                {
                    $counter = (int) substr($jasa->kode, 4, 3) + 1;
                    if ($counter > 9 && $counter < 100)
                        $g->response["result"]["auto_number"] = "JKHS0" . $counter;
                    else if ($counter < 10)
                        $g->response["result"]["auto_number"] = "JKHS00" . $counter;
                    else
                        $g->response["result"]["auto_number"] = "JKHS" . $counter;
                } 
                break;

            case "REFERENSI-KHS-MATERIAL":
                $sql = "SELECT kode FROM referensi_khs WHERE kode LIKE 'MKHS%' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $jasa = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$jasa)
                    $g->response["result"]["auto_number"] = "MKHS001";
                else
                {
                    $counter = (int) substr($jasa->kode, 4, 3) + 1;
                    if ($counter > 9 && $counter < 100)
                        $g->response["result"]["auto_number"] = "MKHS0" . $counter;
                    else if ($counter < 10)
                        $g->response["result"]["auto_number"] = "MKHS00" . $counter;
                    else
                        $g->response["result"]["auto_number"] = "MKHS" . $counter;
                } 
                break;

            case "REFERENSI-BIAYA":
                $sql = "SELECT kode FROM referensi_biaya ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $biaya = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$biaya)
                    $g->response["result"]["auto_number"] = "B0100";
                else
                {
                    $counter = (int) substr($biaya->kode, 1, 2) + 1;
                    if ($counter > 9)
                        $g->response["result"]["auto_number"] = "B" . $counter . "00";
                    else
                        $g->response["result"]["auto_number"] = "B0" . $counter . "00";
                    
                } 
                break;

            case "REFERENSI-POP":
                $sql = "SELECT kode FROM referensi_pop ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $jasa = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$jasa)
                    $g->response["result"]["auto_number"] = "P001";
                else
                {
                    $counter = (int) substr($jasa->kode, 1, 3) + 1;
                    if ($counter > 9 && $counter < 100)
                        $g->response["result"]["auto_number"] = "P0" . $counter;
                    else if ($counter < 10)
                        $g->response["result"]["auto_number"] = "P00" . $counter;
                    else
                        $g->response["result"]["auto_number"] = "P" . $counter;
                } 
                break;

            case "TOPUP-PETTY-CASH":
                $sql = "SELECT no_bukti FROM petty_cash WHERE arus_kas = 'MASUK' AND tipe = 'TOPUP' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $lastTopup = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$lastTopup)
                    $g->response["result"]["auto_number"] = "TOPUP/00001";
                else
                {
                    $topup = explode("/", $lastTopup->no_bukti);
                    $counter = 1 + (int) $topup[1];
                    $loop = 5 - strlen((string) $counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter = "0" . $counter;

                    $g->response["result"]["auto_number"] = "TOPUP/" . $counter;
                }
                break;

            case "KUITANSI-AKTIVASI-RUTIN":
                if (!isset($params->no_bukti))
                {
                    setResponseStatus(false, "No bukti aktivasi required!");
                    setExitResponse();
                    Flight::stop();
                }

                $sql = "SELECT counter FROM auto_number WHERE number_type = 'KUITANSI-AKTIVASI-RUTIN' LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "00001" . "/" . str_replace("/", "-", $params->no_bukti);
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = $counter . "/" . str_replace("/", "-", $params->no_bukti);
                }
                break;

            case "KUITANSI-SERPO-RUTIN":
                if (!isset($params->no_bukti))
                {
                    setResponseStatus(false, "No bukti serpo required!");
                    setExitResponse();
                    Flight::stop();
                }

                $sql = "SELECT counter FROM auto_number WHERE number_type = 'KUITANSI-SERPO-RUTIN' LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "00001" . "/" . str_replace("/", "-", $params->no_bukti);
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = $counter . "/" . str_replace("/", "-", $params->no_bukti);
                }
                break;

            case "KUITANSI-SERPO-PEMBELIAN-MATERIAL":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'KUITANSI-SERPO-PEMBELIAN-MATERIAL' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = $counter . "/" . $month . $year;
                }
                break;

            case "KUITANSI-AKTIVASI-SO-JASA":
                if (!isset($params->no_bukti))
                {
                    setResponseStatus(false, "No bukti so jasa required!");
                    setExitResponse();
                    Flight::stop();
                }

                $sql = "SELECT counter FROM auto_number WHERE number_type = 'KUITANSI-AKTIVASI-SO-JASA' LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "00001" . "/" . str_replace("/", "-", $params->no_bukti);
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = $counter . "/" . str_replace("/", "-", $params->no_bukti);
                }
                break;

            case "KUITANSI-AKTIVASI-SO-BIAYA-LAIN":
                if (!isset($params->no_bukti))
                {
                    setResponseStatus(false, "No bukti so biaya lain required!");
                    setExitResponse();
                    Flight::stop();
                }

                $sql = "SELECT counter FROM auto_number WHERE number_type = 'KUITANSI-AKTIVASI-SO-BIAYA-LAIN' LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "00001" . "/" . str_replace("/", "-", $params->no_bukti);
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = $counter . "/" . str_replace("/", "-", $params->no_bukti);
                }
                break;
            
            case "PEMBELIAN-MATERIAL-PUSAT":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'PEMBELIAN-MATERIAL-PUSAT' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "BA-PST/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "BA-PST/" . $counter . "/" . $month . $year;
                }
                break;

            case "IH-TRANSFER":
                $sql = "SELECT counter FROM auto_number WHERE number_type = 'IH-TRANSFER' ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->execute();

                $row = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if (!$row)
                    $g->response["result"]["auto_number"] =  "BA-IH/00001" . "/" . $month . $year;
                else
                {
                    $loop = 5 - strlen($row->counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter .= "0"; 

                    $row->counter++;
                    $counter .= $row->counter;
                    $g->response["result"]["auto_number"] = "BA-IH/" . $counter . "/" . $month . $year;
                }
                break;
        } 
        
        setResponseStatus(true, "Autonumber created!");
    }
    else
        setResponseStatus(false, "Autonumber type not found!");
});

Flight::route("POST|PUT /util/autonumber/update", function() use ($g, $pdo, $params) {

    $params->type = trim(strtoupper($params->type));
    $month = date("m");
    $year = date("y");

    if (isValidAutonumberType($params->type))
    {
        $sql = "SELECT * FROM auto_number 
            WHERE month = '" . $month . "' AND year = '" . $year . "' AND number_type = :tipe";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":tipe", $params->type, PDO::PARAM_STR);
        $pdo->execute();

        $lastNoBukti = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if ($lastNoBukti != NULL)
        {
            $sql = "UPDATE auto_number SET counter = counter + 1, 
                modified_at = NOW(), modified_by = '" . $g->logged->username . "'   
                WHERE month = '" . $month . "' AND year = '" . $year . "' AND number_type = :tipe";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":tipe", $params->type, PDO::PARAM_STR);
            $pdo->execute();
        }
        else
        {
            $data = [
                "counter" => [1, "string"],
                "month" => [$month, "string"],
                "year" => [$year, "string"],
                "number_type" => [$params->type, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("auto_number", $data);
        }

        setResponseStatus(true, "Autonumber updated!");
    }
    else
        setResponseStatus(false, "Autonumber type not found!");
});

// AUTO NUMBER NO BUKTI KUITANSI
Flight::route("POST|PUT /util/autonumber/kuitansi/create", function() use ($g, $pdo, $params) {

    $params->type = trim(strtoupper($params->type));

    if (isValidAutonumberType($params->type))
    {
        $g->response["result"]["type"] = $params->type;

        $sql = "SELECT * FROM auto_number_kuitansi WHERE type = :type AND no_bukti = :no_bukti";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":type", $params->type, PDO::PARAM_STR);
        $pdo->sth->bindParam(":no_bukti", $params->no_bukti, PDO::PARAM_STR);
        $pdo->execute();

        $lastNoBukti = $pdo->sth->fetch(PDO::FETCH_OBJ);
        $sepBukti = explode("/", $params->no_bukti);
        $g->response["result"]["no_bukti"] = $params->no_bukti;

        if (!$lastNoBukti)
        {
            switch ($params->type)
            {
                case "AKTIVASI-RUTIN": 
                    $g->response["result"]["no_kuitansi"] = "00001/AKTIVASI-" . $sepBukti[1];
                    break;

                case "AKTIVASI-SO-JASA": 
                    $g->response["result"]["no_kuitansi"] = "00001/SOJ-" . $sepBukti[1];
                    break;
            }
        }
        else
        {
            $counter = (int) $lastNoBukti->counter + 1;
            switch ($params->type)
            {
                case "AKTIVASI-RUTIN":
                    $loop = 5 - strlen($counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter = "0" . $counter; 

                    $g->response["result"]["no_kuitansi"] = $counter . "/AKTIVASI-" . $sepBukti[1];
                    break;

                case "AKTIVASI-SO-JASA":
                    $loop = 5 - strlen($counter);
                    for ($i = 0; $i < $loop; $i++)
                        $counter = "0" . $counter; 

                    $g->response["result"]["no_kuitansi"] = $counter . "/SOJ-" . $sepBukti[1];
                    break;
            }
        }
        
        setResponseStatus(true, "Autonumber kuitansi created!");
    }
    else
        setResponseStatus(false, "Autonumber kuitansi type not found!");
});

Flight::route("POST|PUT /util/autonumber/kuitansi/update", function() use ($g, $pdo, $params) {

    $params->type = trim(strtoupper($params->type));

    if (isValidAutonumberType($params->type))
    {
        $sql = "SELECT * FROM auto_number_kuitansi WHERE type = :type AND no_bukti = :no_bukti";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":type", $params->type, PDO::PARAM_STR);
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
                "type" => [$params->type, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("auto_number_kuitansi", $data);
        }

        setResponseStatus(true, "Autonumber kuitansi updated!");
    }
    else
        setResponseStatus(false, "Autonumber kuitansi type not found!");
});