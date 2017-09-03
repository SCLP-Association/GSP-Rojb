<?php

Flight::route("POST|PUT /stock/pembelian/material/input", function() use ($g, $pdo, $params) {

    // dari pdf tidak ada hubungan nya dengan petty cash, nanti harus ditanyakan lagi

    // check no_ba
    if (!isRowExist("pusat_material_pembelian", "no_ba", $params->no_ba))
    {
        // check no_po
        if (!isRowExist("pusat_material_pembelian", "no_po", $params->no_po))
        {
            $pdo->con->beginTransaction();
            $rollback = false;
            $rollbackMessage = "";

            // insert into pusat_material_pembelian
            $data = [
                "no_ba" => [$params->no_ba, "string"],
                "tgl" => [$params->tgl, "string"],
                "supplier" => [$params->supplier, "string"],
                "no_po" => [$params->no_po, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];

            $pdo->insert("pusat_material_pembelian", $data);

            foreach ($params->detail as $id => $material)
            {
                if (strlen($material->kode_material) == 0 || strlen($material->jenis_material) == 0 || 
                    strlen($material->satuan_material) == 0 || strlen($material->qty) == 0)
                {
                    $rollback = true;
                    $rollbackMessage = "Detail material tidak boleh kosong!";
                    break;
                }

                if ((int) $material->qty == 0)
                {
                    $rollback = true;
                    $rollbackMessage = "Qty material ".$material->kode_material." tidak boleh nol!";
                    break;
                }

                // check each material code
                if (!isRowExist("referensi_material", "kode", $material->kode_material))
                {
                    $rollback = true;
                    $rollbackMessage = "Kode material " . $material->kode_material . " tidak ditemukan!";
                    break;
                }

                $data = [
                    "kode_material" => [$material->kode_material, "string"],
                    "jenis_material" => [$material->jenis_material, "string"],
                    "satuan_material" => [$material->satuan_material, "string"],
                    "no_ba" => [$params->no_ba, "string"],
                    "qty" => [$material->qty, "int"],
                    "nilai_beli" => [$material->nilai_beli, "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("pusat_material_pembelian_detail", $data);

                // stock masing2 material bertambah => masuk ke regional PST
                $sql = "SELECT * FROM material_stock WHERE kode_material = :kode AND kode_regional = 'PST' LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":kode", $material->kode_material, PDO::PARAM_STR);
                $pdo->execute();

                $stock = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if ($stock)
                {
                    $sql = "UPDATE material_stock SET qty = qty + :new_qty, modified_at = NOW(), modified_by = :mo  
                        WHERE kode_material = :kode AND kode_regional = 'PST'";
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":new_qty", $material->qty, PDO::PARAM_INT);
                    $pdo->sth->bindParam(":kode", $material->kode_material, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);
                    $pdo->execute();
                }
                else
                {
                    $dataStock = [
                        "kode_material" => [$material->kode_material, "string"],
                        "kode_regional" => ["PST", "string"],
                        "qty" => [$material->qty, "int"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("material_stock", $dataStock);
                }

                // add stock history
                $dataHistoryStock = [
                    "kode_material" => [$material->kode_material, "string"],
                    "kode_regional" => ["PST", "string"],
                    "qty_change" => [$material->qty, "int"],
                    "tipe_transaksi" => ["PEMBELIAN-MATERIAL-PUSAT", "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("material_stock_history", $dataHistoryStock);
            }

            if (!$rollback)
            {
                updateAutonumber("PEMBELIAN-MATERIAL-PUSAT", $g, $pdo);
                $pdo->con->commit();

                setResponseStatus(true, "Pembelian material pusat berhasil dibuat!");
            }
            else
            {
                $pdo->con->rollBack();
                setResponseStatus(false, $rollbackMessage);
            }
        }
        else
            setResponseStatus(false, "No PO sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "No BA sudah ada sebelumnya!");
});

Flight::route("POST|PUT /stock/pembelian/material/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    $from_date = 0;
    if (isValidDate($params->from_date))
        $from_date = $params->from_date . " 00:00:00.000000";

    $to_date = 0;
    if (isValidDate($params->to_date))
        $to_date = $params->to_date . " 23:59:59.999999";        

    // count total data
    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM pusat_material_pembelian 
            WHERE no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM pusat_material_pembelian 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM pusat_material_pembelian 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM pusat_material_pembelian 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->execute();

    $result = $pdo->sth->fetch(PDO::FETCH_OBJ);

    $g->response["result"]["total_all"] = (int) $result->total_data;
    $divPage = $g->response["result"]["total_all"] / $g->response["result"]["limit"];

    $totalPage = 1;
    if ($divPage < 1)
        $totalPage = 1;
    else
    {
        $totalPage = floor($divPage);
        $modPage = $g->response["result"]["total_all"] % $g->response["result"]["limit"];
        if ($modPage > 0)
            $totalPage++;
    }
    $g->response["result"]["total_page"] = $totalPage;

    // get data
    $page = ($page == 1) ? 0 : ((int) $page - 1) * (int) $limit;

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM pusat_material_pembelian 
            WHERE no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM pusat_material_pembelian 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM pusat_material_pembelian 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM pusat_material_pembelian 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND (created_at BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        $row["detail"] = [];
        $sql = "SELECT * FROM pusat_material_pembelian_detail WHERE no_ba = :no_ba";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_ba", $row["no_ba"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Pembelian Material pusat(s) retrieved!");
});

Flight::route("POST|PUT /stock/status/material", function() use ($g, $pdo, $params) {

    $error = false;
    $g->response["result"]["data"] = [];
    if ($params->kode_material != "")
    {
        if (!isRowExist("referensi_material", "kode", $params->kode_material))
        {
            $error = true;
        }
    }

    if (!$error)
    {
        // get all referensi material regional
        $sql = "SELECT * FROM referensi_material_regional";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->execute();

        $regional = $pdo->sth->fetchAll();

        // get distinct kode in material_stock
        if ($params->kode_material != "")
            $sql = "SELECT DISTINCT kode_material AS kode_material FROM material_stock WHERE kode_material = '" . $params->kode_material . "' ORDER BY kode_material ASC";
        else
            $sql = "SELECT DISTINCT kode_material AS kode_material FROM material_stock ORDER BY kode_material ASC";
        
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->execute();

        $listMaterial = [];
        // loop berapa material yg ada
        $idx = 0;
        while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
        {
            $listMaterial[$idx] = [];
            $listMaterial[$idx]["kode"] = $row["kode_material"];

            // get jenis material
            $pdoMat = new MyPdo();
            $sqlMat = "SELECT jenis FROM referensi_material WHERE kode = '" . $row["kode_material"] . "' LIMIT 1";
            $pdoMat->sth = $pdoMat->con->prepare($sqlMat);
            $pdoMat->execute();
            $singleMaterial = $pdoMat->sth->fetch(PDO::FETCH_OBJ);
            $listMaterial[$idx]["jenis_material"] = $singleMaterial->jenis;

            foreach ($regional as $idy => $reg)
            {
                // get stock for each regional
                $pdoStock = new MyPdo();
                $sqlStock = "SELECT * FROM material_stock WHERE kode_regional = '" . $reg["kode"] . "' 
                    AND kode_material = '" . $row["kode_material"] . "' AND active = 1";
                $pdoStock->sth = $pdoStock->con->prepare($sqlStock);
                $pdoStock->execute();
                $sStock = $pdoStock->sth->fetch(PDO::FETCH_OBJ);

                $qtyTmp = 0;
                if ($sStock)
                    $qtyTmp = (int) $sStock->qty;

                $listMaterial[$idx][$reg["kode"]] = $qtyTmp;
            }

            $idx++;
        }

        $g->response["result"]["data"] = $listMaterial;
        setResponseStatus(true, $idx . " Status Material(s) retrieved!");
    }
    else
        setResponseStatus(false, "Kode material " . $params->kode_material . " tidak ditemukan");
});

Flight::route("POST|PUT /stock/ih/transfer/create", function() use ($g, $pdo, $params) {

    // check masing masing stock material di regional
    $errorStock = false;
    $errorStockMsg = "";
    foreach ($params->detail as $idx => $material)
    {
        $sql = "SELECT * FROM material_stock WHERE kode_regional = :kode_reg AND kode_material = :kode_mat LIMIT 1";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":kode_mat", $material->kode_material, PDO::PARAM_STR);
        $pdo->sth->bindParam(":kode_reg", $params->regional_sumber, PDO::PARAM_STR);
        $pdo->execute();

        $stock = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if ($stock)
        {
            if ($stock->qty - $material->qty < 0)
            {
                $errorStock = true;
                $errorStockMsg = "Stock material " . $material->kode_material . " pada " . $params->regional_sumber . " tidak mencukupi";
            }
        }
        else
        {
            $errorStock = true;
            $errorStockMsg = "Stock material " . $material->kode_material . " pada " . $params->regional_sumber . " tidak mencukupi / tidak ditemukan";
        }
    }

    if (!$errorStock)
    {
        // check no ba
        if (!isRowExist("pusat_material_pembelian", "no_ba", $params->no_ba))
        {
            // check referensi regional material tujuan
            if (isRowExist("referensi_material_regional", "kode", $params->regional_tujuan))
            {
                // check referensi regional material sumber
                if (isRowExist("referensi_material_regional", "kode", $params->regional_sumber))
                {
                    $pdo->con->beginTransaction();
                    $rollback = false;
                    $rollbackMessage = "";

                    // get nama regional sumber & tujuan
                    // $sql = "SELECT nama FROM referensi_material_regional WHERE kode = :kode_sumber";
                    // $pdo->sth = $pdo->con->prepare($sql);
                    // $pdo->sth->bindParam(":kode_sumber", $params->regional_sumber, PDO::PARAM_STR);
                    // $pdo->execute();
                    // $regSumber = $pdo->sth->fetch(PDO::FETCH_OBJ);

                    // $sql = "SELECT nama FROM referensi_material_regional WHERE kode = :kode_tujuan";
                    // $pdo->sth = $pdo->con->prepare($sql);
                    // $pdo->sth->bindParam(":kode_tujuan", $params->regional_tujuan, PDO::PARAM_STR);
                    // $pdo->execute();
                    // $regTujuan = $pdo->sth->fetch(PDO::FETCH_OBJ);

                    // insert ih transfer 
                    $data = [
                        "no_ba" => [$params->no_ba, "string"],
                        "tgl" => [$params->tgl, "string"],
                        "regional_sumber" => [$params->regional_sumber, "string"],
                        "regional_tujuan" => [$params->regional_tujuan, "string"],
                        "penerima" => [$params->penerima, "string"],
                        "user" => [$params->user, "string"]
                    ];

                    $pdo->insert("stock_ih_transfer", $data);
                    foreach ($params->detail as $id => $material)
                    {
                        if (strlen($material->kode_material) == 0 || strlen($material->jenis_material) == 0 || 
                            strlen($material->satuan_material) == 0 || strlen($material->qty) == 0)
                        {
                            $rollback = true;
                            $rollbackMessage = "Detail material tidak boleh kosong!";
                            break;
                        }

                        if ((int) $material->qty == 0)
                        {
                            $rollback = true;
                            $rollbackMessage = "Qty material ".$material->kode_material." tidak boleh nol!";
                            break;
                        }

                        // check each material code
                        if (!isRowExist("referensi_material", "kode", $material->kode_material))
                        {
                            $rollback = true;
                            $rollbackMessage = "Kode material " . $material->kode_material . " tidak ditemukan!";
                            break;
                        }

                        $data = [
                            "kode_material" => [$material->kode_material, "string"],
                            "jenis_material" => [$material->jenis_material, "string"],
                            "satuan_material" => [$material->satuan_material, "string"],
                            "no_ba" => [$params->no_ba, "string"],
                            "qty" => [$material->qty, "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("stock_ih_transfer_detail", $data);

                        // + stock pada regional tujuan bertambah
                        $sql = "SELECT * FROM material_stock WHERE kode_material = :kode_mat AND kode_regional = :kode_reg LIMIT 1";
                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":kode_mat", $material->kode_material, PDO::PARAM_STR);
                        $pdo->sth->bindParam(":kode_reg", $params->regional_tujuan, PDO::PARAM_STR);
                        $pdo->execute();

                        $stock = $pdo->sth->fetch(PDO::FETCH_OBJ);
                        if ($stock)
                        {
                            $sql = "UPDATE material_stock SET qty = qty + :new_qty, modified_at = NOW(), modified_by = :mo  
                                WHERE kode_material = :kode_mat AND kode_regional = :kode_reg";
                            $pdo->sth = $pdo->con->prepare($sql);
                            $pdo->sth->bindParam(":new_qty", $material->qty, PDO::PARAM_INT);
                            $pdo->sth->bindParam(":kode_mat", $material->kode_material, PDO::PARAM_STR);
                            $pdo->sth->bindParam(":kode_reg", $params->regional_tujuan, PDO::PARAM_STR);
                            $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);
                            $pdo->execute();
                        }
                        else
                        {
                            $dataStock = [
                                "kode_material" => [$material->kode_material, "string"],
                                "kode_regional" => [$params->regional_tujuan, "string"],
                                "qty" => [$material->qty, "int"],
                                "created_by" => [$g->logged->username, "string"]
                            ];
                            $pdo->insert("material_stock", $dataStock);
                        }
                        // add stock history regional tujuan
                        $dataHistoryStock = [
                            "kode_material" => [$material->kode_material, "string"],
                            "kode_regional" => [$params->regional_tujuan, "string"],
                            "qty_change" => [$material->qty, "int"],
                            "tipe_transaksi" => ["IH-TRANSFER", "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("material_stock_history", $dataHistoryStock);

                        // - stock pada regional sumber berkurang
                        $sql = "UPDATE material_stock SET qty = qty - :new_qty, modified_at = NOW(), modified_by = :mo  
                                WHERE kode_material = :kode_mat AND kode_regional = :kode_reg";
                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":new_qty", $material->qty, PDO::PARAM_INT);
                        $pdo->sth->bindParam(":kode_mat", $material->kode_material, PDO::PARAM_STR);
                        $pdo->sth->bindParam(":kode_reg", $params->regional_sumber, PDO::PARAM_STR);
                        $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);
                        $pdo->execute();

                        // add stock history regional sumber
                        $dataHistoryStock = [
                            "kode_material" => [$material->kode_material, "string"],
                            "kode_regional" => [$params->regional_sumber, "string"],
                            "qty_change" => [-($material->qty), "int"],
                            "tipe_transaksi" => ["IH-TRANSFER", "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("material_stock_history", $dataHistoryStock);
                    }

                    if (!$rollback)
                    {
                        updateAutonumber("IH-TRANSFER", $g, $pdo);
                        $pdo->con->commit();

                        setResponseStatus(true, "Ih Transfer berhasil dibuat!");
                    }
                    else
                    {
                        $pdo->con->rollBack();
                        setResponseStatus(false, $rollbackMessage);
                    }
                }
                else
                    setResponseStatus(false, "Regional sumber " . $params->regional_tujuan . " tidak ditemukan!");
            }
            else
                setResponseStatus(false, "Regional tujuan " . $params->regional_tujuan . " tidak ditemukan!");
        }
        else
            setResponseStatus(false, "No BA sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, $errorStockMsg);
});

Flight::route("POST|PUT /stock/ih/transfer/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    $from_date = 0;
    if (isValidDate($params->from_date))
        $from_date = $params->from_date . " 00:00:00.000000";

    $to_date = 0;
    if (isValidDate($params->to_date))
        $to_date = $params->to_date . " 23:59:59.999999";        

    // count total data
    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM stock_ih_transfer  
            WHERE no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM stock_ih_transfer 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM stock_ih_transfer 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_ba) as total_data FROM stock_ih_transfer 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->execute();

    $result = $pdo->sth->fetch(PDO::FETCH_OBJ);

    $g->response["result"]["total_all"] = (int) $result->total_data;
    $divPage = $g->response["result"]["total_all"] / $g->response["result"]["limit"];

    $totalPage = 1;
    if ($divPage < 1)
        $totalPage = 1;
    else
    {
        $totalPage = floor($divPage);
        $modPage = $g->response["result"]["total_all"] % $g->response["result"]["limit"];
        if ($modPage > 0)
            $totalPage++;
    }
    $g->response["result"]["total_page"] = $totalPage;

    // get data
    $page = ($page == 1) ? 0 : ((int) $page - 1) * (int) $limit;

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM stock_ih_transfer 
            WHERE no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM stock_ih_transfer 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM stock_ih_transfer 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM stock_ih_transfer 
            WHERE (no_ba LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND (created_at BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        $row["detail"] = [];
        $sql = "SELECT * FROM stock_ih_transfer_detail WHERE no_ba = :no_ba";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_ba", $row["no_ba"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." IH Transfer(s) retrieved!");
});

Flight::route("POST|PUT /stock/pengeluaran/material/input", function() use ($g, $pdo, $params) {

    requiredFields($params, "no_dokumen, no_bukti, tgl, penerima, token");

    $totalQtyMaterial = 0;
    foreach ($params->detail as $idx => $material)
        $totalQtyMaterial += (int) $material->qty_out;

    if ($totalQtyMaterial > 0)
    {
        // check no_dokumen yaitu no bukti pkb di so
        if (isRowExist("aktivasi_so", "pkb_no_bukti", $params->no_dokumen))
        {
            // check no bukti qout apakah sudah digunakan
            if (!isRowExist("material_pengeluaran", "no_bukti", $params->no_bukti))
            {
                $rollback = false;
                $rollbackMessage = "";
                $pdo->con->beginTransaction();
                // insert pengeluaran material q-out
                $data = [
                    "no_bukti" => [$params->no_bukti, "string"],
                    "no_dokumen" => [$params->no_dokumen, "string"],
                    "type" => ["Q-OUT", "string"],
                    "tgl" => [$params->tgl, "string"],
                    "penerima" => [$params->penerima, "string"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("material_pengeluaran", $data);

                // recursive function for update kertas kerja
                function updateKertasKerja($row, $pdo)
                {
                    global $params, $g;// why this global not working? -> $rollback, $rollbackMessage;

                    $rollbackTmp = false;
                    $rollbackMessageTmp = "";                               

                    // insert kertas kerja, pertama ambil dulu kertas kerja yg masih ada stock nya
                    $sql = "SELECT * FROM material_kertas_kerja 
                        WHERE kode_material = :kode_mat AND jenis_transaksi = 'PEROLEHAN' AND available_tmp_qty > 0 
                        ORDER BY created_at ASC LIMIT 1";
                    
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":kode_mat", $row->kode_material, PDO::PARAM_STR);
                    $pdo->execute();

                    $kertasKerja = $pdo->sth->fetch(PDO::FETCH_OBJ);
                    if ($kertasKerja)
                    {
                        // ambil kertas kerja terakhir untuk ambil total qty dan nilai
                        $sql = "SELECT * FROM material_kertas_kerja WHERE kode_material = :kode_mat 
                            ORDER BY created_at DESC LIMIT 1";

                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":kode_mat", $row->kode_material, PDO::PARAM_STR);
                        $pdo->execute();

                        $lastKertasKerja = $pdo->sth->fetch(PDO::FETCH_OBJ);

                        $sisaQty = $kertasKerja->available_tmp_qty - $row->qty_out;
                        $hpu = $kertasKerja->perolehan_hpu;

                        if ($sisaQty < 0)
                        {
                            // update kertasKerja available_tmp_qty jadi 0, lakukan loop function
                            $sql = "UPDATE material_kertas_kerja SET available_tmp_qty = 0, modified_at = NOW(), 
                                modified_by = :mo_by WHERE id = :id";
                            $pdo->sth = $pdo->con->prepare($sql);
                            $pdo->sth->bindParam(":id", $kertasKerja->id, PDO::PARAM_INT);
                            $pdo->sth->bindParam(":mo_by", $g->logged->username, PDO::PARAM_STR);
                            $pdo->execute();

                            $row->qty_out = -($sisaQty);                                    

                            // insert into kertas kerja
                            $dataKertasKerja = [
                                "no_dokumen" => [$params->no_bukti, "string"],
                                "no_referensi" => [$params->no_so, "string"],
                                "tgl" => [$params->tgl, "string"],
                                "kode_material" => [$row->kode_material, "string"],
                                "jenis_material" => [$row->jenis_material, "string"],
                                "satuan_material" => [$lastKertasKerja->satuan_material, "string"],
                                "deskripsi" => ["PENGGUNAAN MATERIAL AKTIVASI", "string"],
                                "jenis_transaksi" => ["PENGELUARAN", "string"],
                                "jenis_usaha" => ["AKTIVASI", "string"],
                                "available_tmp_qty" => [0, "int"],
                                "pengeluaran_qty" => [$row->qty_out, "int"],
                                "pengeluaran_hpu" => [$hpu, "string"],
                                "pengeluaran_nilai" => [$hpu * $row->qty_out, "int"],
                                "total_nilai" => [$lastKertasKerja->total_nilai - ($hpu * $row->qty_out), "int"],
                                "total_qty" => [$lastKertasKerja->total_qty - $row->qty_out, "int"],
                                "created_by" => [$g->logged->username, "string"]
                            ];
                            $pdo->insert("material_kertas_kerja", $dataKertasKerja);
                            
                            return updateKertasKerja($row, $pdo);
                        }
                        else
                        {
                            // update kertasKerja available_tmp_qty jadi = $sisaQty
                            $sql = "UPDATE material_kertas_kerja SET available_tmp_qty = :sisa, modified_at = NOW(), 
                                modified_by = :mo_by WHERE id = :id";
                            $pdo->sth = $pdo->con->prepare($sql);
                            $pdo->sth->bindParam(":sisa", $sisaQty, PDO::PARAM_INT);
                            $pdo->sth->bindParam(":id", $kertasKerja->id, PDO::PARAM_INT);
                            $pdo->sth->bindParam(":mo_by", $g->logged->username, PDO::PARAM_STR);
                            $pdo->execute();

                            // insert into kertas kerja
                            $dataKertasKerja = [
                                "no_dokumen" => [$params->no_bukti, "string"],
                                "no_referensi" => [$params->no_so, "string"],
                                "tgl" => [$params->tgl, "string"],
                                "kode_material" => [$row->kode_material, "string"],
                                "jenis_material" => [$row->jenis_material, "string"],
                                "satuan_material" => [$lastKertasKerja->satuan_material, "string"],
                                "deskripsi" => ["PENGGUNAAN MATERIAL AKTIVASI", "string"],
                                "jenis_transaksi" => ["PENGELUARAN", "string"],
                                "jenis_usaha" => ["AKTIVASI", "string"],
                                "available_tmp_qty" => [0, "int"],
                                "pengeluaran_qty" => [$row->qty_out, "int"],
                                "pengeluaran_hpu" => [$hpu, "string"],
                                "pengeluaran_nilai" => [$hpu * $row->qty_out, "int"],
                                "total_nilai" => [$lastKertasKerja->total_nilai - ($hpu * $row->qty_out), "int"],
                                "total_qty" => [$lastKertasKerja->total_qty - $row->qty_out, "int"],
                                "created_by" => [$g->logged->username, "string"]
                            ];
                            $pdo->insert("material_kertas_kerja", $dataKertasKerja);
                        }
                    }
                    else
                    {
                        // it should be exit from function and roll back all transaction
                        $rollbackTmp = true;
                        $rollbackMessageTmp = "Qty " . $row->kode_material . " pada kertas kerja tidak mecukupi";
                    }

                    return ["rollback" => $rollbackTmp, "rollbackMessage" => $rollbackMessageTmp];
                }

                foreach ($params->detail as $idx => $row)
                {
                    // ----- start material stock validation
                    // check detail material
                    if (strlen($row->kode_material) == 0 || strlen($row->jenis_material) == 0 || strlen($row->qty_out) == 0)
                    {
                        $rollback = true;
                        $rollbackMessage = "Detail material tidak boleh kosong!";
                        break;
                    }

                    // check qty material
                    // if ((int) $row->qty_out == 0)
                    // {
                    //     $rollback = true;
                    //     $rollbackMessage = "Qty material ".$row->kode_material." tidak boleh nol!";
                    //     break;
                    // }
                    // karena mungkin sebagian ada yang kosong

                    // check each material code
                    if (!isRowExist("referensi_material", "kode", $row->kode_material))
                    {
                        $rollback = true;
                        $rollbackMessage = "Kode material " . $row->kode_material . " tidak ditemukan!";
                        break;
                    }

                    // jika request nya 0 tidak perlu dimasukan ke history
                    if ((int) $row->qty_out > 0)
                    {
                        // check qty out tidak boleh lebih besar dari qty request
                        $sql = "SELECT qty, qty_out FROM aktivasi_so_pkb_material WHERE  
                            kode_material = :kode_mat AND no_bukti = :no_dokumen LIMIT 1";
                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":kode_mat", $row->kode_material, PDO::PARAM_STR);
                        $pdo->sth->bindParam(":no_dokumen", $params->no_dokumen, PDO::PARAM_STR);
                        $pdo->execute();

                        $request = $pdo->sth->fetch(PDO::FETCH_OBJ);

                        if ($request)
                        {
                            if ((int) $request->qty - (int) $request->qty_out < (int) $row->qty_out)
                            {
                                $rollback = true;
                                $rollbackMessage = "Qty out " . $row->kode_material . " tidak boleh melebihi request";

                                if ((int) $request->qty_out > 0)
                                    $rollbackMessage .= ", pernah dilakukan Qty out sebelumnya";

                                break;
                            }
                        }
                        else
                        {
                            $rollback = true;
                            $rollbackMessage = "Material request " . $row->kode_material . " tidak ditemukan pada PKB " . $params->no_dokumen;
                            break;
                        }

                        // check apakah stock material pada regional mencukupi
                        $sql = "SELECT qty FROM material_stock WHERE kode_material = :kode_mat AND kode_regional = :kode_reg AND active = 1 LIMIT 1";
                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":kode_mat", $row->kode_material, PDO::PARAM_STR);
                        $pdo->sth->bindParam(":kode_reg", $g->logged->regional, PDO::PARAM_STR);
                        $pdo->execute();

                        $stock = $pdo->sth->fetch(PDO::FETCH_OBJ);
                        if ($stock)
                        {
                            if ((int) $stock->qty - (int) $row->qty_out < 0)
                            {
                                $rollback = true;
                                $rollbackMessage = "Stock material " . $row->kode_material . " pada " . $g->logged->regional . " tidak mencukupi";
                                break;
                            }
                        }
                        else
                        {
                            $rollback = true;
                            $rollbackMessage = "Stock material " . $row->kode_material . " pada " . $g->logged->regional . " belum tersedia";
                            break;
                        }

                        // ----- end material stock validation

                        // insert material_pengeluaran_detail
                        $dataPengeluaran = [
                            "no_bukti" => [$params->no_bukti, "string"],
                            "kode_material" => [$row->kode_material, "string"],
                            "jenis_material" => [$row->jenis_material, "string"],  
                            "qty_out_stock" => [$row->qty_out, "int"],    
                            "qty_out" => [$row->qty_out, "int"],              
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("material_pengeluaran_detail", $dataPengeluaran);

                        // jika stock mencukupi maka stock regional dikurangi
                        $sql = "UPDATE material_stock SET qty = qty - :qty_out, modified_at = NOW(), modified_by = :mo 
                            WHERE kode_material = :kode AND kode_regional = :reg AND active = 1";
                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":qty_out", $row->qty_out, PDO::PARAM_INT);
                        $pdo->sth->bindParam(":kode", $row->kode_material, PDO::PARAM_STR);
                        $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);                
                        $pdo->sth->bindParam(":reg", $g->logged->regional, PDO::PARAM_STR);
                        $pdo->execute();

                        // tambah history stock (pengeluaran material)
                        $dataHistoryStock = [
                            "kode_material" => [$row->kode_material, "string"],
                            "kode_regional" => [$g->logged->regional, "string"],
                            "qty_change" => [-($row->qty_out), "int"],
                            "tipe_transaksi" => ["PENGELUAARAN-MATERIAL", "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("material_stock_history", $dataHistoryStock);

                        // update qty_out pada table aktivasi_so_pkb_material
                        $sql = "UPDATE aktivasi_so_pkb_material SET qty_out = IF (qty_out IS NULL, 0, qty_out) + :q_out, modified_at = NOW(), modified_by = :mo  
                            WHERE kode_material = :kode AND no_bukti = :no_dokumen";

                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":q_out", $row->qty_out, PDO::PARAM_INT);
                        $pdo->sth->bindParam(":kode", $row->kode_material, PDO::PARAM_STR);
                        $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);                
                        $pdo->sth->bindParam(":no_dokumen", $params->no_dokumen, PDO::PARAM_STR);
                        $pdo->execute();

                        $rKertasKerja = updateKertasKerja($row, $pdo);

                        $rollback = $rKertasKerja["rollback"];
                        $rollbackMessage = $rKertasKerja["rollbackMessage"];
                    }
                }

                //$rollback = true;
                if (!$rollback)
                {
                    // update qtyout autonumber
                    updateAutonumber("QOUT-PENGELUARAN-MATERIAL", $g, $pdo);

                    $pdo->con->commit();
                    setResponseStatus(true, "Qout pengeluaran material berhasil dibuat! " . (string) $rollback);
                }
                else
                {
                    $pdo->con->rollBack();
                    setResponseStatus(false, $rollbackMessage);
                }
            }
            else
                setResponseStatus(false, "No Bukti Q-OUT sudah digunakan!");
        }
        else
            setResponseStatus(false, "No Dokumen PKB tidak ditemukan!");
    }
    else
        setResponseStatus(false, "Total material tidak boleh nol!");
});

Flight::route("POST|PUT /stock/pengeluaran/material/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    $from_date = 0;
    if (isValidDate($params->from_date))
        $from_date = $params->from_date . " 00:00:00.000000";

    $to_date = 0;
    if (isValidDate($params->to_date))
        $to_date = $params->to_date . " 23:59:59.999999";        

    // count total data
    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM material_pengeluaran 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM material_pengeluaran 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM material_pengeluaran 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM material_pengeluaran 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->execute();

    $result = $pdo->sth->fetch(PDO::FETCH_OBJ);

    $g->response["result"]["total_all"] = (int) $result->total_data;
    $divPage = $g->response["result"]["total_all"] / $g->response["result"]["limit"];

    $totalPage = 1;
    if ($divPage < 1)
        $totalPage = 1;
    else
    {
        $totalPage = floor($divPage);
        $modPage = $g->response["result"]["total_all"] % $g->response["result"]["limit"];
        if ($modPage > 0)
            $totalPage++;
    }
    $g->response["result"]["total_page"] = $totalPage;

    // get data
    $page = ($page == 1) ? 0 : ((int) $page - 1) * (int) $limit;

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM material_pengeluaran 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM material_pengeluaran 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM material_pengeluaran 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM material_pengeluaran 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND (created_at BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        $row["material"] = [];
        $sql = "SELECT * FROM material_pengeluaran_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Pengeluaran Material pusat(s) retrieved!");
});

Flight::route("POST|PUT /stock/available_qout", function () use ($g, $pdo, $params) {

    requiredFields($params, "pkb_no_bukti, kode_material, page, token");
    $params->page = (int) $params->page - 1;
    // input nya kode material, no_pkb, order by created_at desc
    $sql = "SELECT b.id, b.jenis_material, b.kode_material, b.no_bukti, b.qty_out_stock, b.qty_out  
        FROM material_pengeluaran a JOIN material_pengeluaran_detail b ON a.no_bukti = b.no_bukti
        WHERE a.no_dokumen = :no_pkb AND b.kode_material = :kode_material AND b.qty_out_stock > 0
        AND a.type = 'Q-OUT' ORDER BY b.created_at DESC LIMIT :page, 1";

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_pkb", $params->pkb_no_bukti, PDO::PARAM_STR);
    $pdo->sth->bindParam(":kode_material", $params->kode_material, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $params->page, PDO::PARAM_INT);
    $pdo->execute();

    $row = $pdo->sth->fetch(PDO::FETCH_OBJ);

    if ($row)
    {
        $g->response["result"] = $row;
        setResponseStatus(true, "Available qty-out to be retur!");
    }
    else
    {
        $g->response["result"] = [];
        setResponseStatus(false, "Available qty-out to be retur empty!");
    }
});

Flight::route("POST|PUT /stock/qty_retur_input", function() use ($g, $pdo, $params) {

    // check no_bukti retur
    if (!isRowExist("material_pengeluaran", "no_bukti", $params->no_bukti_retur))
    {
        if (isRowExist("material_pengeluaran", "no_dokumen", $params->no_bukti_pkb))
        {
            $rollback = false;
            $pdo->con->beginTransaction();

            // insert material pengeluaran
            $data = [
                "no_bukti" => [$params->no_bukti_retur, "string"],
                "no_dokumen" => [$params->no_bukti_pkb, "string"],
                "type" => ["RETUR", "string"],
                "tgl" => [$params->tgl, "string"],
                "penerima" => [$params->peretur, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];

            $pdo->insert("material_pengeluaran", $data);

            // ambil hpu terakhir dari kertas kerja => untuk ambil nilai pengeluaran_hpu
            $sql = "SELECT satuan_material, pengeluaran_hpu FROM material_kertas_kerja WHERE kode_material = :kode 
                AND pengeluaran_hpu > 0 AND pengeluaran_hpu IS NOT NULL 
                ORDER BY created_at DESC LIMIT 1";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":kode", $params->kode_material, PDO::PARAM_STR);
            $pdo->execute();

            $kertasKerja = $pdo->sth->fetch(PDO::FETCH_OBJ);

            // ambil row kertas kerja terakhir untuk mengambil total nilai dan total qty terakhir
            $sql = "SELECT total_nilai, total_qty FROM material_kertas_kerja WHERE kode_material = :kode 
                ORDER BY created_at DESC LIMIT 1";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":kode", $params->kode_material, PDO::PARAM_STR);
            $pdo->execute();

            $kk = $pdo->sth->fetch(PDO::FETCH_OBJ);

            // update material_pengeluaran_detail qty_out_stock, qty_out_stock adalah temporary sisa stock yg bisa di retur
            $totalQtyRetur = 0;
            foreach ($params->detail as $idx => $row)
            {
                $sql = "UPDATE material_pengeluaran_detail SET qty_out_stock = qty_out_stock - :qty_retur 
                    WHERE no_bukti = :no_bukti AND kode_material = :kode_material";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":qty_retur", $row->qty_retur, PDO::PARAM_INT);
                $pdo->sth->bindParam(":no_bukti", $row->no_bukti_qout, PDO::PARAM_STR);
                $pdo->sth->bindParam(":kode_material", $params->kode_material, PDO::PARAM_STR);
                $pdo->execute();

                $totalQtyRetur += (int) $row->qty_retur;

                $kk->total_nilai += (int) $kertasKerja->pengeluaran_hpu;

                // insert ke kertas kerja
                $dataKertasKerja = [
                    "no_dokumen" => [$params->no_bukti_retur, "string"],
                    "no_referensi" => [$params->no_so, "string"],
                    "tgl" => [$params->tgl, "string"],
                    "kode_material" => [$params->kode_material, "string"],
                    "jenis_material" => [$params->jenis_material, "string"],
                    "satuan_material" => [$kertasKerja->satuan_material, "string"],
                    "deskripsi" => ["RETUR " . $row->no_bukti_qout, "string"],
                    "jenis_transaksi" => ["PEROLEHAN", "string"],
                    "jenis_usaha" => ["AKTIVASI-RETUR", "string"],
                    "available_tmp_qty" => [$row->qty_retur, "string"],
                    "perolehan_qty" => [$row->qty_retur, "int"],
                    "perolehan_hpu" => [$kertasKerja->pengeluaran_hpu, "int"],
                    "total_nilai" => [$kk->total_nilai, "int"],
                    "total_qty" => [$kk->total_qty + $row->qty_retur, "int"],
                    "perolehan_nilai" => [($kertasKerja->pengeluaran_hpu * $row->qty_retur), "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];

                $pdo->insert("material_kertas_kerja", $dataKertasKerja);

                // insert ke stock history - tambah history stock (pengeluaran material)
                $dataHistoryStock = [
                    "kode_material" => [$params->kode_material, "string"],
                    "kode_regional" => [$g->logged->regional, "string"],
                    "qty_change" => [$row->qty_retur, "int"],
                    "tipe_transaksi" => ["RETUR-MATERIAL", "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("material_stock_history", $dataHistoryStock);
            }

            // stock material di HO harus bertambah
            $sql = "UPDATE material_stock SET qty = qty + :qty_retur WHERE kode_material = :kode_mat AND kode_regional = 'HO'";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":qty_retur", $totalQtyRetur, PDO::PARAM_STR);
            $pdo->sth->bindParam(":kode_mat", $params->kode_material, PDO::PARAM_STR);
            $pdo->execute();

            // insert material_pengeluaran detail
            $data = [
                "no_bukti" => [$params->no_bukti_retur, "string"],
                "kode_material" => [$params->kode_material, "string"],
                "jenis_material" => [$params->jenis_material, "string"],
                "retur" => [$totalQtyRetur, "int"]
            ];

            $pdo->insert("material_pengeluaran_detail", $data);

            // update aktivasi_pkb_item
            $sql = "UPDATE aktivasi_so_pkb_material SET retur = IF (retur IS NULL OR retur = 0, :retur, retur + :retur), modified_at = NOW(), modified_by = :mb 
                WHERE no_bukti = :no_pkb AND kode_material = :kode";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":retur", $totalQtyRetur, PDO::PARAM_INT);
            $pdo->sth->bindParam(":mb", $g->logged->username, PDO::PARAM_STR);
            $pdo->sth->bindParam(":no_pkb", $params->no_bukti_pkb, PDO::PARAM_STR);
            $pdo->sth->bindParam(":kode", $params->kode_material, PDO::PARAM_STR);
            $pdo->execute();

            // update auto number retur
            updateAutonumber("BA-RETUR", $g, $pdo);

            $pdo->con->commit();

            setResponseStatus(true, "Input retur berhasil!");
        }
        else
            setResponseStatus(false, "No bukti pkb ".$params->no_bukti_pkb." tidak ditemukan!");
    }
    else
        setResponseStatus(false, "No bukti retur ".$params->no_bukti_retur." sudah digunakan!");
});

Flight::route("POST|PUT /stock/qty_loss_input", function() use ($g, $pdo, $params) {

    // check no_bukti kerugian
    if (!isRowExist("material_pengeluaran", "no_bukti", $params->no_bukti_kerugian))
    {
        if (isRowExist("material_pengeluaran", "no_dokumen", $params->no_bukti_pkb))
        {
            $rollback = false;
            $pdo->con->beginTransaction();

            // insert material pengeluaran
            $data = [
                "no_bukti" => [$params->no_bukti_kerugian, "string"],
                "no_dokumen" => [$params->no_bukti_pkb, "string"],
                "type" => ["KERUGIAN", "string"],
                "tgl" => [$params->tgl, "string"],
                "penerima" => [$params->no_baps, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];

            $pdo->insert("material_pengeluaran", $data);

            // ambil hpu terakhir dari kertas kerja => untuk ambil nilai pengeluaran_hpu
            $sql = "SELECT satuan_material, pengeluaran_hpu, total_qty, total_nilai FROM material_kertas_kerja 
                WHERE kode_material = :kode AND pengeluaran_hpu > 0 AND pengeluaran_hpu IS NOT NULL 
                ORDER BY created_at DESC LIMIT 1";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":kode", $params->kode_material, PDO::PARAM_STR);
            $pdo->execute();

            $kertasKerja = $pdo->sth->fetch(PDO::FETCH_OBJ);

            $totalQtyKerugian = 0;
            foreach ($params->detail as $idx => $row)
            {
                $sql = "UPDATE material_pengeluaran_detail SET qty_out_stock = qty_out_stock - :qty_loss 
                    WHERE no_bukti = :no_bukti AND kode_material = :kode_material";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":qty_loss", $row->qty_loss, PDO::PARAM_INT);
                $pdo->sth->bindParam(":no_bukti", $row->no_bukti_qout, PDO::PARAM_STR);
                $pdo->sth->bindParam(":kode_material", $params->kode_material, PDO::PARAM_STR);
                $pdo->execute();

                $totalQtyKerugian += (int) $row->qty_loss;

                // insert ke kertas kerja
                $dataKertasKerja = [
                    "no_dokumen" => [$params->no_bukti_kerugian, "string"],
                    "no_referensi" => [$params->no_so, "string"],
                    "tgl" => [$params->tgl, "string"],
                    "kode_material" => [$params->kode_material, "string"],
                    "jenis_material" => [$params->jenis_material, "string"],
                    "satuan_material" => [$kertasKerja->satuan_material, "string"],
                    "deskripsi" => ["KERUGIAN " . $row->no_bukti_qout, "string"],
                    "jenis_transaksi" => ["KERUGIAN", "string"],
                    "jenis_usaha" => ["AKTIVASI-KERUGIAN", "string"],
                    "available_tmp_qty" => [0, "string"],
                    "perolehan_qty" => [$row->qty_loss, "int"],
                    "perolehan_hpu" => [$kertasKerja->pengeluaran_hpu, "int"],
                    "pengeluaran_nilai" => [($kertasKerja->pengeluaran_hpu * $row->qty_loss), "int"],
                    "pengeluaran_qty" => [$row->qty_loss, "int"],
                    "pengeluaran_hpu" => [$kertasKerja->pengeluaran_hpu, "int"],
                    "perolehan_nilai" => [($kertasKerja->pengeluaran_hpu * $row->qty_loss), "int"],
                    "total_nilai" => [$kertasKerja->total_nilai, "int"],
                    "total_qty" => [$kertasKerja->total_qty, "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];

                $pdo->insert("material_kertas_kerja", $dataKertasKerja);
            }

            // stock history tidak perlu di update -> karena tidak ada perubahan qty

            // stock/status material tidak bertambah

            // insert material_pengeluaran detail
            $data = [
                "no_bukti" => [$params->no_bukti_kerugian, "string"],
                "kode_material" => [$params->kode_material, "string"],
                "jenis_material" => [$params->jenis_material, "string"],
                "loss" => [$totalQtyKerugian, "int"]
            ];

            $pdo->insert("material_pengeluaran_detail", $data);

            // update aktivasi_pkb_item
            $sql = "UPDATE aktivasi_so_pkb_material SET loss = IF (loss IS NULL OR loss = 0, :loss, loss + :loss), modified_at = NOW(), modified_by = :mb 
                WHERE no_bukti = :no_pkb AND kode_material = :kode";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":loss", $totalQtyKerugian, PDO::PARAM_INT);
            $pdo->sth->bindParam(":mb", $g->logged->username, PDO::PARAM_STR);
            $pdo->sth->bindParam(":no_pkb", $params->no_bukti_pkb, PDO::PARAM_STR);
            $pdo->sth->bindParam(":kode", $params->kode_material, PDO::PARAM_STR);
            $pdo->execute();

            // update auto number kerugian
            updateAutonumber("BA-KERUGIAN", $g, $pdo);

            $pdo->con->commit();

            setResponseStatus(true, "Input kerugian berhasil!");
        }
        else
            setResponseStatus(false, "No bukti pkb ".$params->no_bukti_pkb." tidak ditemukan!");
    }
    else
        setResponseStatus(false, "No bukti kerugian ".$params->no_bukti_kerugian." sudah digunakan!");
});

Flight::route("POST|PUT /stock/saldo/tercatat", function() use ($g, $pdo, $params) {

    $sql = "SELECT distinct(kode_material), jenis_material, satuan_material, total_qty, total_nilai FROM
            (SELECT * FROM material_kertas_kerja GROUP BY kode_material, id, total_qty
            ORDER BY kode_material, id DESC) AS temp
            GROUP BY kode_material";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->execute();

    $g->response["result"]["total"] = 0;
    $g->response["result"]["data"] = [];
    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Saldo tercatat(s) retrieved!");
});

Flight::route("POST|PUT /stock/kertas/kerja/@kode", function($kode) use ($g, $pdo, $params) {

    $from_date = 0;
    if (isValidDate($params->from_date))
        $from_date = $params->from_date . " 00:00:00.000000";

    $to_date = 0;
    if (isValidDate($params->to_date))
        $to_date = $params->to_date . " 23:59:59.999999";   

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM material_kertas_kerja WHERE kode_material = :kode ORDER BY created_at ASC";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM material_kertas_kerja WHERE kode_material = :kode AND created_at >= '".$from_date."' ORDER BY created_at ASC";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM material_kertas_kerja WHERE kode_material = :kode AND created_at <= '".$to_date."' ORDER BY created_at ASC";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM material_kertas_kerja WHERE kode_material = :kode AND (created_at BETWEEN '".$from_date."' AND '".$to_date."') ORDER BY created_at ASC";

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode);
    $pdo->execute();

    $g->response["result"]["data"] = $pdo->sth->fetchAll(PDO::FETCH_OBJ);
    setResponseStatus(true, "Kertas kerja " . $kode . " retrieved");
});