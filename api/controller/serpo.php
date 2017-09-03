<?php

Flight::route("POST|PUT /serpo/rutin/create", function() use ($g, $pdo, $params) {

    requiredFields($params, "no_bukti, tgl, kode_biaya, jenis_biaya, token");

    // hitung total pengeluaran
    $outcome = 0;
    foreach ($params->detail as $idx => $desk) 
        $outcome += (int) $desk->nilai;
    
    // check saldo petty cash
    if ($outcome <= getSaldoPettyCash())
    { 
        // check no bukti
        if (!isRowExist("serpo_rutin", "no_bukti", $params->no_bukti))
        {
            // check kode biaya
            if (isRowExist("referensi_biaya", "kode", $params->kode_biaya))
            {
                // check no bukti kuitansi
                if (!isRowExist("serpo_rutin", "no_kuitansi", $params->no_kuitansi) || strlen($params->no_kuitansi) == 0)
                {
                    $rollback = false;
                    $pdo->con->beginTransaction();
                    // insert serpo rutin
                    $data = [
                        "no_bukti" => [$params->no_bukti, "string"],
                        "tgl" => [$params->tgl, "string"],
                        "kode_biaya" => [$params->kode_biaya, "string"],
                        "jenis_biaya" => [$params->jenis_biaya, "string"],
                        "no_kuitansi" => [$params->no_kuitansi, "string"],
                        "tgl_kuitansi" => [$params->tgl_kuitansi, "string"],
                        "admin_kuitansi" => [$params->admin_kuitansi, "string"],
                        "penerima_kuitansi" => [$params->penerima_kuitansi, "string"],
                        "id_kuitansi" => [$params->id_kuitansi, "string"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("serpo_rutin", $data);

                    // insert serpo rutin ke petty cash
                    $data = [
                        "no_bukti" => [$params->no_bukti, "string"],
                        "tgl" => [$params->tgl, "string"],
                        "kode" => [$params->kode_biaya, "string"],
                        "jenis" => [$params->jenis_biaya, "string"],
                        "arus_kas" => ["KELUAR", "string"],
                        "tipe" => ["PENGELUARAN-SERPO", "string"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("petty_cash", $data);

                    // insert detail serpo rutin deskripsi transaksi
                    foreach ($params->detail as $idx => $desk) 
                    {
                        if (strlen($desk->deskripsi) == 0 || strlen($desk->nilai) == 0)
                        {
                            $rollback = true;
                            break;
                        }

                        // insert into serpo_rutin_detail
                        $data = [
                            "no_bukti" => [$params->no_bukti, "string"],
                            "deskripsi" => [$desk->deskripsi, "string"],
                            "nilai" => [$desk->nilai, "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("serpo_rutin_detail", $data);

                        // insert into petty_cash_detail
                        $pdo->insert("petty_cash_detail", $data);
                    }

                    // petty cash saldo berkurang 
                    $sql = "UPDATE petty_cash_saldo SET saldo = saldo - :pengeluaran";
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":pengeluaran", $outcome, PDO::PARAM_INT);
                    $pdo->execute();

                    if (!$rollback)
                    {
                        updateAutonumber("SERPO-RUTIN", $g, $pdo);

                        // update auto number no kuitansi jika ada
                        if (strlen($params->no_kuitansi) > 10)
                            updateAutonumber("KUITANSI-SERPO-RUTIN", $g, $pdo);

                        $pdo->con->commit();

                        setResponseStatus(true, "Serpo rutin berhasil dibuat!");
                    }
                    else
                    {
                        $pdo->con->rollBack();

                        setResponseStatus(false, "Detail serpo rutin tidak boleh kosong!");
                    }
                }
                else
                    setResponseStatus(false, "No kuitansi sudah digunakan!");
            }
            else
                setResponseStatus(false, "Kode biaya " . $params->kode_biaya . " tidak ditemukan!");
        }
        else
            setResponseStatus(false, "No bukti serpo rutin sudah digunakan!");
    }
    else
        setResponseStatus(false, "Saldo petty cash tidak mencukupi, lakukan topup!");
});

Flight::route("POST|PUT /serpo/rutin/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
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
        $sql = "SELECT * FROM serpo_rutin 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM serpo_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_rutin 
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
        $row["detail"] = [];

        $sql = "SELECT * FROM serpo_rutin_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Serpo Rutin(s) retrieved!");
});

Flight::route("POST|PUT /serpo/rutin/search_by/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {
    
    $key = $params->key;
    $g->response["result"]["key"] = $params->key;
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";

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
        $sql = "SELECT * FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')  
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')  
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%'  
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%' 
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

        $sql = "SELECT * FROM serpo_rutin_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Serpo Rutin(s) retrieved!");
});

Flight::route("POST|PUT /serpo/pembelian/material/input", function() use ($g, $pdo, $params) {
    requiredFields($params, "no_bukti, tgl, supplier, lokasi, token");

    // hitung total pengeluaran
    $outcome = 0;
    foreach ($params->detail as $idx => $desk) 
        $outcome += (int) $desk->nilai_beli;

    // check saldo petty cash
    if ($outcome <= getSaldoPettyCash())
    { 
        // check no bukti
        if (!isRowExist("serpo_material_pembelian", "no_bukti", $params->no_bukti))
        {
            $rollback = false;
            $rollbackMessage = "";
            $pdo->con->beginTransaction();
            // insert serpo material pembelian
            $data = [
                "no_bukti" => [$params->no_bukti, "string"],
                "tgl" => [$params->tgl, "string"],
                "supplier" => [$params->supplier, "string"],
                "lokasi" => [$params->lokasi, "string"],                
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("serpo_material_pembelian", $data);

            // apakah masuk petty cash atau tidak? ya masuk
            $data = [
                "no_bukti" => [$params->no_bukti, "string"],
                "tgl" => [$params->tgl, "string"],
                "kode" => ["B1800", "string"],
                "jenis" => ["PEMBELIAN MATERIAL", "string"],
                "arus_kas" => ["KELUAR", "string"],
                "tipe" => ["PEMBELIAN-SERPO-MATERIAL", "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("petty_cash", $data);

            // petty cash saldo berkurang 
            $sql = "UPDATE petty_cash_saldo SET saldo = saldo - :pengeluaran";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":pengeluaran", $outcome, PDO::PARAM_INT);
            $pdo->execute();

            // insert detail serpo material pembelian
            foreach ($params->detail as $idx => $material) 
            {
                // check detail material serpo
                if (strlen($material->kode_material) == 0 || strlen($material->nilai_beli) == 0 || 
                    strlen($material->jenis_material) == 0 || strlen($material->qty) == 0 || 
                    strlen($material->satuan_material) == 0)
                {
                    $rollback = true;
                    $rollbackMessage = "Detail material tidak boleh kosong!";
                    break;
                }

                // check qty & nilai_beli
                if ((int) $material->qty == 0 || (int) $material->nilai_beli == 0)
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

                $deskripsi = $material->kode_material . ", " . $material->jenis_material . ", Q: ";
                $deskripsi .= $material->qty . ", NILAI BELI: " . $material->nilai_beli;
                // insert into serpo material pembelian detail
                $data = [
                    "no_bukti" => [$params->no_bukti, "string"],
                    "kode_material" => [$material->kode_material, "string"],
                    "jenis_material" => [$material->jenis_material, "string"],
                    "satuan_material" => [$material->satuan_material, "string"],
                    "qty" => [$material->qty, "int"],
                    "nilai_beli" => [$material->nilai_beli, "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("serpo_material_pembelian_detail", $data);

                // stock masing2 material bertambah => masuk ke regional HO
                $sql = "SELECT * FROM material_stock WHERE kode_material = :kode AND kode_regional = 'HO' LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":kode", $material->kode_material, PDO::PARAM_STR);
                $pdo->execute();

                $stock = $pdo->sth->fetch(PDO::FETCH_OBJ);
                if ($stock)
                {
                    $sql = "UPDATE material_stock SET qty = qty + :new_qty, modified_at = NOW(), modified_by = :mo  
                        WHERE kode_material = :kode AND kode_regional = 'HO'";
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
                        "kode_regional" => ["HO", "string"],
                        "qty" => [$material->qty, "int"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("material_stock", $dataStock);
                }

                // add stock history
                $dataHistoryStock = [
                    "kode_material" => [$material->kode_material, "string"],
                    "kode_regional" => ["HO", "string"],
                    "qty_change" => [$material->qty, "int"],
                    "tipe_transaksi" => ["SERPO-PEMBELIAN-MATERIAL", "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("material_stock_history", $dataHistoryStock);

                // insert into petty_cash_detail
                $data = [
                    "no_bukti" => [$params->no_bukti, "string"],
                    "deskripsi" => [$deskripsi, "string"],
                    "nilai" => [$material->nilai_beli, "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("petty_cash_detail", $data);

                // insert into kertas kerja
                $sql = "SELECT * FROM material_kertas_kerja WHERE kode_material = :kode_mat ORDER BY created_at DESC LIMIT 1";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":kode_mat", $material->kode_material, PDO::PARAM_STR);
                $pdo->execute();

                $kertasKerja = $pdo->sth->fetch(PDO::FETCH_OBJ);
                
                $totalNilai = $material->nilai_beli;
                $totalQty = $material->qty;

                $hpu = (int) $material->nilai_beli / $material->qty;
                if ($kertasKerja)
                {
                    $totalNilai += (int) $kertasKerja->total_nilai;
                    $totalQty += (int) $kertasKerja->total_qty;
                }

                $dataKertasKerja = [
                    "no_dokumen" => [$params->no_bukti, "string"],
                    "tgl" => [$params->tgl, "string"],
                    "kode_material" => [$material->kode_material, "string"],
                    "jenis_material" => [$material->jenis_material, "string"],
                    "satuan_material" => [$material->satuan_material, "string"],
                    "deskripsi" => ["PEMBELIAN MATERIAL SERPO", "string"],
                    "jenis_transaksi" => ["PEROLEHAN", "string"],
                    "jenis_usaha" => ["SERPO", "string"],
                    "available_tmp_qty" => [$material->qty, "int"],
                    "perolehan_qty" => [$material->qty, "int"],
                    "perolehan_hpu" => [$hpu, "string"],
                    "perolehan_nilai" => [$material->nilai_beli, "string"],
                    "total_nilai" => [$totalNilai, "string"],
                    "total_qty" => [$totalQty, "string"],
                    "created_by" => [$g->logged->username, "string"]
                ];

                $pdo->insert("material_kertas_kerja", $dataKertasKerja);
            }

            if (!$rollback)
            {
                // update serpo pembelian material autonumber
                updateAutonumber("SERPO-PEMBELIAN-MATERIAL", $g, $pdo);

                $pdo->con->commit();
                setResponseStatus(true, "Serpo pembelian material berhasil dibuat!");
            }
            else
            {
                $pdo->con->rollBack();
                setResponseStatus(false, $rollbackMessage);
            }
        }
        else
            setResponseStatus(false, "No bukti serpo pembelian material sudah digunakan!");
    }
    else
        setResponseStatus(false, "Saldo petty cash tidak mencukupi, lakukan topup!");
});

Flight::route("POST|PUT /serpo/pembelian/material/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
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
        $sql = "SELECT * FROM serpo_material_pembelian 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM serpo_material_pembelian 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_material_pembelian 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_material_pembelian 
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

        $row["detail"] = [];
        $sql = "SELECT * FROM serpo_material_pembelian_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Serpo Pembelian Material(s) retrieved!");
});

Flight::route("POST|PUT /serpo/pembelian/material/add_kuitansi", function() use ($g, $pdo, $params) {
    requiredFields($params, "no_bukti, no_kuitansi, token");

    // check no_bukti
    if (isRowExist("serpo_material_pembelian", "no_bukti", $params->no_bukti))
    {
        // check unique no kuitansi
        if (!isRowExist("serpo_material_pembelian", "no_kuitansi", $params->no_kuitansi))
        {
            // check no kuitansi apakah sudah di input
            $sql = "SELECT no_kuitansi FROM serpo_material_pembelian WHERE no_bukti = :no_bukti LIMIT 1";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":no_bukti", $params->no_bukti, PDO::PARAM_STR);
            $pdo->execute();

            $row = $pdo->sth->fetch(PDO::FETCH_OBJ);

            if (strlen($row->no_kuitansi) == 0)
            {
                $sql = "UPDATE serpo_material_pembelian SET no_kuitansi = :no_kuitansi WHERE no_bukti = :no_bukti";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":no_kuitansi", $params->no_kuitansi, PDO::PARAM_STR);
                $pdo->sth->bindParam(":no_bukti", $params->no_bukti, PDO::PARAM_STR);
                $pdo->execute();

                if ($pdo->sth->rowCount() > 0)
                {
                    // update auto number kuitansi
                    updateAutonumber("KUITANSI-SERPO-PEMBELIAN-MATERIAL", $g, $pdo);

                    setResponseStatus(true, "No kuitansi berhasil dibuat!");
                }
                else
                    setResponseStatus(false, "Tidak ada perubahan data!");
            }
            else
                setResponseStatus(false, "No kuitansi serpo pembelian material ada!");
        }
        else
            setResponseStatus(false, "No kuitansi serpo pembelian material sudah digunakan!");
    }
    else
        setResponseStatus(false, "No bukti serpo pembelian material tidak ditemukan!");
});

Flight::route("POST|PUT /serpo/pembelian/material/search_by/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {
    
    $key = $params->key;
    $g->response["result"]["key"] = $params->key;
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";

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
        $sql = "SELECT * FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')  
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')  
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%'  
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_material_pembelian 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%' 
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

        $sql = "SELECT * FROM serpo_material_pembelian_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Serpo Pembelian Material (s) retrieved!");
});

Flight::route("POST|PUT /serpo/penggunaan/material/input", function() use ($g, $pdo, $params) {

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

    requiredFields($params, "no_ar, tgl, kode_regional, token");

    // check masing masing stock material di regional
    $errorStock = false;
    $errorStockMsg = "";
    foreach ($params->detail as $idx => $material)
    {
        $sql = "SELECT * FROM material_stock WHERE kode_regional = 'HO' AND kode_material = :kode_mat LIMIT 1";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":kode_mat", $material->kode_material, PDO::PARAM_STR);
        $pdo->execute();

        $stock = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if ($stock)
        {
            if ($stock->qty - $material->qty < 0)
            {
                $errorStock = true;
                $errorStockMsg = "Stock material " . $material->kode_material . " pada HO tidak mencukupi";
            }
        }
        else
        {
            $errorStock = true;
            $errorStockMsg = "Stock material " . $material->kode_material . " pada HO tidak mencukupi / tidak ditemukan";
        }
    }

    if (!$errorStock)
    {
        // check no ba
        if (!isRowExist("serpo_material_penggunaan", "no_ar", $params->no_ar))
        {
            // check referensi material regional
            if (isRowExist("referensi_material_regional", "kode", $params->kode_regional))
            {
                $rollback = false;
                $rollbackMessage = "";
                $pdo->con->beginTransaction();
                // insert serpo material penggunaan
                $data = [
                    "no_ar" => [$params->no_ar, "string"],
                    "tgl" => [$params->tgl, "string"],
                    "regional" => [$params->kode_regional, "string"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("serpo_material_penggunaan", $data);

                // insert detail serpo material penggunaan
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
                        "no_ar" => [$params->no_ar, "string"],
                        "kode_material" => [$material->kode_material, "string"],
                        "jenis_material" => [$material->jenis_material, "string"],
                        "satuan_material" => [$material->satuan_material, "string"],
                        "qty" => [$material->qty, "int"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("serpo_material_penggunaan_detail", $data);

                    // stock HO di pindah ke regional tujuan
                    // stock HO berkurang
                    $sql = "UPDATE material_stock SET qty = qty - :new_qty, modified_at = NOW(), modified_by = :mo WHERE kode_material = :kode AND kode_regional = 'HO'";
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":new_qty", $material->qty, PDO::PARAM_INT);
                    $pdo->sth->bindParam(":kode", $material->kode_material, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":mo", $g->logged->username, PDO::PARAM_STR);
                    $pdo->execute();

                    // add stock history HO
                    $dataHistoryStock = [
                        "kode_material" => [$material->kode_material, "string"],
                        "kode_regional" => ["HO", "string"],
                        "qty_change" => [-($material->qty), "int"],
                        "tipe_transaksi" => ["SERPO-PENGGUNAAN-MATERIAL", "int"],
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
                        "tipe_transaksi" => ["SERPO-PENGGUNAAN-MATERIAL", "int"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("material_stock_history", $dataHistoryStock);
                }

                if (!$rollback)
                {
                    $pdo->con->commit();
                    setResponseStatus(true, "Serpo penggunaan material berhasil dibuat!");
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
            setResponseStatus(false, "No AR sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, $errorStockMsg);
});

Flight::route("POST|PUT /serpo/penggunaan/material/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_ar) as total_data FROM serpo_material_penggunaan 
            WHERE no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_ar) as total_data FROM serpo_material_penggunaan 
            WHERE (no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_ar) as total_data FROM serpo_material_penggunaan 
            WHERE (no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_ar) as total_data FROM serpo_material_penggunaan 
            WHERE (no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";

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
        $sql = "SELECT * FROM serpo_material_penggunaan 
            WHERE no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM serpo_material_penggunaan 
            WHERE (no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_material_penggunaan 
            WHERE (no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM serpo_material_penggunaan 
            WHERE (no_ar LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
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
        $sql = "SELECT * FROM serpo_material_penggunaan_detail WHERE no_ar = :no_ar";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_ar", $row["no_ar"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Serpo Penggunaan Material(s) retrieved!");
});