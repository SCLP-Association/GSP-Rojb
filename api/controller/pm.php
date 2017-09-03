<?php

Flight::route("POST|PUT /pm/material/input", function() use ($g, $pdo, $params) {

    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING --------------------------------- 
    // ini harus di perbaiki pengecekan stock dan pengurangan stocknya
    // yang berkurang adalah stock regional yg diinput, material adalah bahan baku
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING ---------------------------------
    // --------------------- WARNING --------------------------------- 

    requiredFields($params, "no_bukti, jenis, tgl, kode_regional, deskripsi, token");

    // check masing masing stock material di regional
    $errorStock = false;
    $errorStockMsg = "";
    foreach ($params->detail as $idx => $material)
    {
        $sql = "SELECT * FROM material_stock WHERE kode_regional = 'PST' AND kode_material = :kode_mat LIMIT 1";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":kode_mat", $material->kode_material, PDO::PARAM_STR);
        $pdo->execute();

        $stock = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if ($stock)
        {
            if ($stock->qty - $material->qty < 0)
            {
                $errorStock = true;
                $errorStockMsg = "Stock material " . $material->kode_material . " pada PST tidak mencukupi";
            }
        }
        else
        {
            $errorStock = true;
            $errorStockMsg = "Stock material " . $material->kode_material . " pada PST tidak mencukupi / tidak ditemukan";
        }
    }

    if (!$errorStock)
    {
        // check no ba
        if (!isRowExist("pm_material", "no_bukti", $params->no_bukti))
        {
            // check referensi material regional
            if (isRowExist("referensi_material_regional", "kode", $params->kode_regional))
            {
                $rollback = false;
                $rollbackMessage = "";
                $pdo->con->beginTransaction();
                // insert pm material (penggunaan)
                $data = [
                    "no_bukti" => [$params->no_bukti, "string"],
                    "tgl" => [$params->tgl, "string"],
                    "jenis" => [$params->jenis, "string"],
                    "pop" => [$params->pop, "pop"],
                    "regional" => [$params->kode_regional, "string"],
                    "tikor" => [$params->tikor, "string"],
                    "deskripsi" => [$params->deskripsi, "string"],
                    "lokasi" => [$params->lokasi, "string"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("pm_material", $data);

                // insert detail pm material detail
                foreach ($params->detail as $idx => $material) 
                {
                    // check detail material serpo
                    if (strlen($material->kode_material) == 0 || strlen($material->jenis_material) == 0 || 
                        strlen($material->qty) == 0 || strlen($material->satuan_material) == 0)
                    {
                        $rollback = true;
                        $rollbackMessage = "Detail material tidak boleh kosong!";
                        break;
                    }

                    // check qty & nilai_beli
                    if ((int) $material->qty == 0)
                    {
                        $rollback = true;
                        $rollbackMessage = "Qty dan nilai beli tidak boleh nol!";
                        break;
                    }

                    // check if material kode exist
                    if (!isRowExist("referensi_material", "kode", $material->kode_material))
                    {
                        $rollback = true;
                        $rollbackMessage = "Kode material " . $material->kode_material . " tidak ditemukan!";
                        break;
                    }

                    // insert into serpo material penggunaan detail
                    $data = [
                        "no_bukti" => [$params->no_bukti, "string"],
                        "kode_material" => [$material->kode_material, "string"],
                        "jenis_material" => [$material->jenis_material, "string"],
                        "satuan_material" => [$material->satuan_material, "string"],
                        "qty" => [$material->qty, "int"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("pm_material_detail", $data);

                    // stock PST di pindah ke regional tujuan
                    // stock PST berkurang
                    $sql = "UPDATE material_stock SET qty = qty - :new_qty, modified_at = NOW(), modified_by = :mo WHERE kode_material = :kode AND kode_regional = 'PST'";
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":new_qty", $material->qty, PDO::PARAM_INT);
                    $pdo->sth->bindParam(":kode", $material->kode_material, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);
                    $pdo->execute();

                    // add stock history PST
                    $dataHistoryStock = [
                        "kode_material" => [$material->kode_material, "string"],
                        "kode_regional" => ["PST", "string"],
                        "qty_change" => [-($material->qty), "int"],
                        "tipe_transaksi" => ["PM-PENGGUNAAN-MATERIAL", "int"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("material_stock_history", $dataHistoryStock);

                    // stock tujuan regional bertambah
                    $sql = "UPDATE material_stock SET qty = qty + :new_qty, modified_at = NOW(), modified_by = :mo WHERE kode_material = :kode AND kode_regional = :kode_reg";
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":new_qty", $material->qty, PDO::PARAM_INT);
                    $pdo->sth->bindParam(":kode", $material->kode_material, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":kode_reg", $material->kode_regional, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);
                    $pdo->execute();

                    // add stock history regional tujuan
                    $dataHistoryStock = [
                        "kode_material" => [$material->kode_material, "string"],
                        "kode_regional" => [$params->kode_regional, "string"],
                        "qty_change" => [$material->qty, "int"],
                        "tipe_transaksi" => ["PM-PENGGUNAAN-MATERIAL", "int"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("material_stock_history", $dataHistoryStock);
                }

                if (!$rollback)
                {
                    // update auto_number
                    updateAutonumber("PM-PENGGUNAAN-MATERIAL", $g, $pdo);

                    $pdo->con->commit();
                    setResponseStatus(true, "PM penggunaan material berhasil dibuat!");
                }
                else
                {
                    $pdo->con->rollBack();
                    setResponseStatus(false, $rollbackMessage);
                }
            }
            else
                setResponseStatus(false, "Kode regional " . $params->kode_regional . " tidak ditemukan!");
        }
        else
            setResponseStatus(false, "No Bukti sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, $errorStockMsg);
});

Flight::route("POST|PUT /pm/material/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM pm_material 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM pm_material 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM pm_material 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM pm_material 
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
        $sql = "SELECT * FROM pm_material 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM pm_material 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM pm_material 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM pm_material 
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

        $sql = "SELECT nama FROM referensi_material_regional WHERE kode = :kode LIMIT 1";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":kode", $row["regional"], PDO::PARAM_STR);
        $pdoChild->execute();

        $regional = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        $row["nama_regional"] = $regional->nama;

        $row["detail"] = [];
        $sql = "SELECT * FROM pm_material_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." PM Material(s) retrieved!");
});