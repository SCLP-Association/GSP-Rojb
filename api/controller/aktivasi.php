<?php
// AKTIVASI RUTIN: UI 100 %
Flight::route("POST|PUT /aktivasi/rutin/create", function() use ($g, $pdo, $params) {

    requiredFields($params, "no_bukti, tgl, kode_biaya, jenis_biaya, token");

    // hitung total pengeluaran
    $outcome = 0;
    foreach ($params->detail as $idx => $desk) 
        $outcome += (int) $desk->nilai;
    
    // check saldo petty cash
    if ($outcome <= getSaldoPettyCash())
    { 
        // check no bukti
        if (!isRowExist("aktivasi_rutin", "no_bukti", $params->no_bukti))
        {
            // check kode biaya
            if (isRowExist("referensi_biaya", "kode", $params->kode_biaya))
            {
                // check no bukti kuitansi
                if (!isRowExist("aktivasi_rutin", "no_kuitansi", $params->no_kuitansi) || strlen($params->no_kuitansi) == 0)
                {
                    $rollback = false;
                    $pdo->con->beginTransaction();
                    // insert aktivasi rutin
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
                    $pdo->insert("aktivasi_rutin", $data);

                    // insert aktivasi rutin ke petty cash
                    $data = [
                        "no_bukti" => [$params->no_bukti, "string"],
                        "tgl" => [$params->tgl, "string"],
                        "kode" => [$params->kode_biaya, "string"],
                        "jenis" => [$params->jenis_biaya, "string"],
                        "arus_kas" => ["KELUAR", "string"],
                        "tipe" => ["PENGELUARAN-AKTIVASI", "string"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("petty_cash", $data);

                    // insert detail aktivasi rutin deskripsi transaksi
                    foreach ($params->detail as $idx => $desk) 
                    {
                        if (strlen($desk->deskripsi) == 0 || strlen($desk->nilai) == 0)
                        {
                            $rollback = true;
                            break;
                        }

                        // insert into aktivasi_rutin_detail
                        $data = [
                            "no_bukti" => [$params->no_bukti, "string"],
                            "deskripsi" => [$desk->deskripsi, "string"],
                            "nilai" => [$desk->nilai, "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("aktivasi_rutin_detail", $data);

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
                        updateAutonumber("AKTIVASI-RUTIN", $g, $pdo);

                        // update auto number no kuitansi jika ada
                        if (strlen($params->no_kuitansi) > 10)
                            updateAutonumber("KUITANSI-AKTIVASI-RUTIN", $g, $pdo);

                        $pdo->con->commit();

                        setResponseStatus(true, "Aktivasi rutin berhasil dibuat!");
                    }
                    else
                    {
                        $pdo->con->rollBack();

                        setResponseStatus(false, "Detail aktivasi rutin tidak boleh kosong!");
                    }
                }
                else
                    setResponseStatus(false, "No kuitansi sudah digunakan!");
            }
            else
                setResponseStatus(false, "Kode biaya " . $params->kode_biaya . " tidak ditemukan!");
        }
        else
            setResponseStatus(false, "No bukti aktivasi rutin sudah digunakan!");
    }
    else
        setResponseStatus(false, "Saldo petty cash tidak mencukupi, lakukan topup!");
});

Flight::route("POST|PUT /aktivasi/rutin/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
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
        $sql = "SELECT * FROM aktivasi_rutin 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_rutin 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_rutin 
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

        $sql = "SELECT * FROM aktivasi_rutin_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi(s) retrieved!");
});

Flight::route("POST|PUT /aktivasi/rutin/search_by/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_rutin 
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
        $sql = "SELECT * FROM aktivasi_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')  
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')  
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_rutin 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%'  
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_rutin 
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

        $sql = "SELECT * FROM aktivasi_rutin_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi(s) retrieved!");
});

// AKTIVASI SO: UI 100 %
Flight::route("POST|PUT /aktivasi/so/create", function() use ($g, $pdo, $params) {
    // check if no so exist
    requiredFields($params, "no_so, pekerjaan, tgl, ptl");
    $params->pekerjaan = trim(strtoupper($params->pekerjaan));

    // check so in aktivasi_so
    if (!isRowExist("aktivasi_so", "no_so", $params->no_so))
    {
        // check so in wip_aktivasi
        if (!isRowExist("wip_aktivasi", "so_no", $params->no_so))
        {
            $pdo->con->beginTransaction();

            // insert to aktivasi so
            $data = [
                "no_so" => [$params->no_so, "string"],
                "tgl" => [$params->tgl, "string"],
                "ptl" => [$params->ptl, "string"],
                "pekerjaan" => [$params->pekerjaan, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("aktivasi_so", $data);

            // insert to wip aktivasi
            $data = [
                "so_no" => [$params->no_so, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("wip_aktivasi", $data);

            $g->response["result"] = [
                "no_so" => $params->no_so,
                "tgl" => $params->tgl,
                "ptl" => $params->ptl,
                "pekerjaan" => $params->pekerjaan,
                "created_by" => $g->logged->username
            ];
            $pdo->con->commit();
            
            setResponseStatus(true, "Aktivasi SO berhasil dibuat!");
        }
        else
            setResponseStatus(false, "No so sudah digunakan pada wip aktivasi!");
    }
    else
        setResponseStatus(false, "No so sudah digunakan!");
});

Flight::route("POST|PUT /aktivasi/so/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {

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
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE no_so LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR pekerjaan LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR pekerjaan LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR pekerjaan LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR pekerjaan LIKE CONCAT('%', :key, '%')) AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";

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
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE aso.no_so LIKE CONCAT('%', :key, '%') OR aso.tgl LIKE CONCAT('%', :key, '%') OR aso.pekerjaan LIKE CONCAT('%', :key, '%') 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE (aso.no_so LIKE CONCAT('%', :key, '%') OR aso.tgl LIKE CONCAT('%', :key, '%') OR aso.pekerjaan LIKE CONCAT('%', :key, '%')) 
            AND aso.created_at >= '".$from_date."' 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE (aso.no_so LIKE CONCAT('%', :key, '%') OR aso.tgl LIKE CONCAT('%', :key, '%') OR aso.pekerjaan LIKE CONCAT('%', :key, '%')) 
            AND aso.created_at <= '".$to_date."' 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE (aso.no_so LIKE CONCAT('%', :key, '%') OR aso.tgl LIKE CONCAT('%', :key, '%') OR aso.pekerjaan LIKE CONCAT('%', :key, '%')) 
            AND (aso.created_at BETWEEN '".$from_date."' AND '".$to_date."') 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        // get pkb
        $sql = "SELECT * FROM aktivasi_so_pkb_item WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["khs"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["khs"][] = $rowDetail;

        $sql = "SELECT * FROM aktivasi_so_pkb_material WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["material"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO(s) retrieved!");
});

Flight::route("POST|PUT /aktivasi/so/search_by/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {

    $key = $params->key;
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
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
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
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE aso.".$params->search_by." LIKE CONCAT('%', :key, '%') 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE aso.".$params->search_by." LIKE CONCAT('%', :key, '%') 
            AND aso.created_at >= '".$from_date."' 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE aso.".$params->search_by." LIKE CONCAT('%', :key, '%') 
            AND aso.created_at <= '".$to_date."' 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT aso.*, asoj.no_bukti AS no_bukti_so_jasa 
            FROM aktivasi_so aso LEFT JOIN aktivasi_so_jasa asoj ON aso.no_so = asoj.no_so 
            WHERE aso.".$params->search_by." LIKE CONCAT('%', :key, '%') 
            AND (aso.created_at BETWEEN '".$from_date."' AND '".$to_date."') 
            GROUP BY aso.no_so 
            ORDER BY aso." . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;


    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        // get pkb
        $sql = "SELECT * FROM aktivasi_so_pkb_item WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["khs"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["khs"][] = $rowDetail;

        $sql = "SELECT * FROM aktivasi_so_pkb_material WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["material"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material"][] = $rowDetail;

        // get pkt
        $sql = "SELECT * FROM aktivasi_so_pkt_item WHERE no_pkt = :no_bukti_pkt";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkt", $row["pkt_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["khs_pkt"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["khs_pkt"][] = $rowDetail;

        $sql = "SELECT * FROM aktivasi_so_pkt_material WHERE no_pkt = :no_bukti_pkt";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkt", $row["pkt_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["material_pkt"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material_pkt"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO(s) retrieved!");
});

Flight::route("POST|PUT /aktivasi/so/material/search_by/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {
    
    $key = $params->key;
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
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
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
        $sql = "SELECT * FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND (created_at BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];
    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $row["material"] = [];

        $pdoChild = new MyPdo();
        // get masing-masing material so dari pkb
        $sql = "SELECT kode_material, jenis_material FROM aktivasi_so_pkb_material WHERE no_bukti = :no_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowMaterial = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
        {
            $row["material"][] = [
                "kode_material" => $rowMaterial["kode_material"], 
                "jenis_material" => $rowMaterial["jenis_material"],
                "qout_net" => 0,
                "qty_pkt" => 0,
                "qty_loss" => 0 
            ];
        }

        // get pkb qout-net
        foreach ($row["material"] as $id => $material)
        {
            // hitung qty_out
            $sql = "SELECT SUM(mpd.qty_out) AS qout_net FROM material_pengeluaran AS mp 
                JOIN material_pengeluaran_detail AS mpd ON mp.no_bukti = mpd.no_bukti 
                WHERE mp.type = 'Q-OUT' AND mp.no_dokumen = '".$row["pkb_no_bukti"]."' 
                AND mpd.kode_material = '".$material["kode_material"]."'";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->execute();

            $pkbQout = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

            // hitung qty_retur
            $sql = "SELECT SUM(mpd.retur) AS retur FROM material_pengeluaran AS mp 
                JOIN material_pengeluaran_detail AS mpd ON mp.no_bukti = mpd.no_bukti 
                WHERE mp.type = 'RETUR' AND mp.no_dokumen = '".$row["pkb_no_bukti"]."' 
                AND mpd.kode_material = '".$material["kode_material"]."'";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->execute();

            $pkbRetur = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

            // hitung qty loss => dari kerugian
            $sql = "SELECT SUM(mpd.loss) AS loss FROM material_pengeluaran AS mp 
                JOIN material_pengeluaran_detail AS mpd ON mp.no_bukti = mpd.no_bukti 
                WHERE mp.type = 'KERUGIAN' AND mp.no_dokumen = '".$row["pkb_no_bukti"]."' 
                AND mpd.kode_material = '".$material["kode_material"]."'";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->execute();

            $pkbLoss = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

            $row["material"][$id]["qout_net"] = (int) $pkbQout->qout_net - (int) $pkbRetur->retur - (int) $pkbLoss->loss;
            //$row["material"][$id]["qty_loss"] = 
        }

        // get qty_pkt
        foreach ($row["material"] as $id => $material)
        {
            // mendingan query nya sekaligus semua qty pkt
            $sql = "SELECT qty FROM aktivasi_so_pkt_material WHERE no_pkt = :no_pkt AND kode_material = :kode_material";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->sth->bindParam(":no_pkt", $row["pkt_no_bukti"], PDO::PARAM_STR);
            $pdoChild->sth->bindParam(":kode_material", $material["kode_material"], PDO::PARAM_STR);
            $pdoChild->execute();

            $pktRow = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
            //var_dump($pktRow); exit;
            if ($pktRow != FALSE)
                $row["material"][$id]["qty_pkt"] = $pktRow->qty;
        }

        // get pkt loss total = qout_net - qty_pkt
        foreach ($row["material"] as $id => $material)
        {
            $row["material"][$id]["qty_loss"] = (int) $row["material"][$id]["qout_net"] - $row["material"][$id]["qty_pkt"];
        }

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." SO Material(s) retrieved!");
});

// AKTIVASI SO JASA: UI 100%
Flight::route("POST|PUT /aktivasi/so/jasa/create", function() use ($g, $pdo, $params) {
    // hitung total pengeluaran
    $outcome = 0;
    foreach ($params->detail as $idx => $desk) 
        $outcome += (int) $desk->nilai;

    // check saldo petty cash
    if ($outcome <= getSaldoPettyCash())
    { 
        // check no so
        if (isRowExist("aktivasi_so", "no_so", $params->no_so))
        {
            // check no bukti
            if (!isRowExist("aktivasi_so_jasa", "no_bukti", $params->no_bukti))
            {
                // check no bukti kuitansi
                if (!isRowExist("aktivasi_so_jasa", "no_kuitansi", $params->no_kuitansi) || strlen($params->no_kuitansi) == 0)
                {
                    // no so di wip kuitansi :: wip aktivasi tidak perlu karena merupakan kalkulasi
                    //if (isRowExist("wip_aktivasi", "so_no", $params->no_so))
                    //{
                    $rollback = false;
                    $pdo->con->beginTransaction();
                    // insert aktivasi so jasa
                    $data = [
                        "no_bukti" => [$params->no_bukti, "string"],
                        "no_so" => [$params->no_so, "string"],
                        "tgl" => [$params->tgl, "string"],
                        "no_kuitansi" => [$params->no_kuitansi, "string"],
                        "tgl_kuitansi" => [$params->tgl_kuitansi, "string"],
                        "admin_kuitansi" => [$params->admin_kuitansi, "string"],
                        "penerima_kuitansi" => [$params->penerima_kuitansi, "string"],
                        "id_kuitansi" => [$params->id_kuitansi, "string"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("aktivasi_so_jasa", $data);

                    // insert petty cash
                    $data = [
                        "no_bukti" => [$params->no_bukti, "string"],
                        "tgl" => [$params->tgl, "string"],
                        "kode" => [$params->detail[0]->kode_jasa, "string"],
                        "jenis" => [$params->detail[0]->jenis_jasa, "string"],
                        "arus_kas" => ["KELUAR", "string"],
                        "tipe" => ["PENGELUARAN-AKTIVASI-SO-JASA", "string"],
                        "created_by" => [$g->logged->username, "string"]
                    ];
                    $pdo->insert("petty_cash", $data);

                    // kurangi saldo petty cash
                    $sql = "UPDATE petty_cash_saldo SET saldo = saldo - :pengeluaran";
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":pengeluaran", $outcome, PDO::PARAM_INT);
                    $pdo->execute();

                    // insert detail jasa so transaksi
                    $errorMessage = "";
                    $totalBiayaSO = 0;
                    foreach ($params->detail as $idx => $desk) 
                    {
                        if (strlen($desk->nilai) == 0 || strlen($desk->pelaksana) == 0)
                        {
                            $errorMessage = "Aktivasi SO jasa detail nilai & pelaksana tidak boleh kosong!";
                            $rollback = true;
                            break;
                        }

                        // check if kode biaya exist
                        if (!isRowExist("referensi_jasa", "kode", $desk->kode_jasa))
                        {
                            $errorMessage = "Jenis jasa kode " . $desk->kode_jasa . " tidak ditemukan!";
                            $rollback = true;
                            break;
                        }

                        $data = [
                            "no_bukti" => [$params->no_bukti, "string"],
                            "kode_jasa" => [$desk->kode_jasa, "string"],
                            "jenis_jasa" => [$desk->jenis_jasa, "string"],
                            "deskripsi" => [$desk->deskripsi, "string"],
                            "pelaksana" => [trim(strtoupper($desk->pelaksana)), "string"],
                            "nilai" => [(int) str_replace(".", "", $desk->nilai), "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("aktivasi_so_jasa_detail", $data);

                        // insert petty cash detail
                        $data = [
                            "no_bukti" => [$params->no_bukti, "string"],
                            "deskripsi" => [$desk->pelaksana, "string"],
                            "nilai" => [$desk->nilai, "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("petty_cash_detail", $data);

                        $totalBiayaSO += (int) str_replace(".", "", $desk->nilai);
                    }

                    if (!$rollback)
                    {
                        updateAutonumber("AKTIVASI-SO-JASA", $g, $pdo);

                        // update auto number no kuitansi jika ada
                        if (strlen($params->no_kuitansi) > 10)
                            updateAutonumber("KUITANSI-AKTIVASI-SO-JASA", $g, $pdo);

                        // update nilai jasa so
                        $sql = "UPDATE aktivasi_so SET nilai_jasa = nilai_jasa + :nilai_jasa, modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE no_so = :no_so";
                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":nilai_jasa", $totalBiayaSO, PDO::PARAM_INT);
                        $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                        $pdo->execute();

                        // update nilai so jasa di wip aktivasi
                        // $sql = "UPDATE wip_aktivasi SET so_jasa_total = so_jasa_total + " . $totalBiayaSO . ", 
                        //     modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE so_no = :no_so";
                        // $pdo->sth = $pdo->con->prepare($sql);
                        // $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                        // $pdo->execute();

                        $pdo->con->commit();

                        setResponseStatus(true, "Aktivasi SO jasa berhasil dibuat!");
                    }
                    else
                    {
                        $pdo->con->rollBack();

                        setResponseStatus(false, $errorMessage);
                    }
                    //}
                    //else
                    //    setResponseStatus(false, "No so tidak ditemukan pada wip aktivasi!");
                }
                else
                    setResponseStatus(false, "No kuitansi sudah digunakan!");
            }
            else
                setResponseStatus(false, "Aktivasi SO jasa no bukti sudah digunakan!");
        }
        else
            setResponseStatus(false, "Aktivasi SO no so tidak ditemukan!");
    }
    else
        setResponseStatus(false, "Saldo petty cash tidak mencukupi, lakukan topup!");
});

Flight::route("POST|PUT /aktivasi/so/jasa/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";
    
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
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) 
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

        $sql = "SELECT a.*, mb.jenis FROM aktivasi_so_jasa_detail AS a
            JOIN referensi_jasa AS mb ON mb.kode = a.kode_jasa
            WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO jasa(s) retrieved!");
});

Flight::route("POST|PUT /aktivasi/so/jasa/search_by/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {
    
    $key = $params->key;
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_jasa 
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
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_jasa 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
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

        $sql = "SELECT a.*, mb.jenis FROM aktivasi_so_jasa_detail AS a
            JOIN referensi_jasa AS mb ON mb.kode = a.kode_jasa
            WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO jasa(s) retrieved!");
});

// AKTIVASI SO BIAYA LAIN: UI 100%
Flight::route("POST|PUT /aktivasi/so/biaya_lain/create", function() use ($g, $pdo, $params) {

    requiredFields($params, "no_bukti, tgl, kode_biaya, jenis_biaya, token");

    // hitung total pengeluaran
    $outcome = 0;
    foreach ($params->detail as $idx => $desk) 
        $outcome += (int) $desk->nilai;

    // check saldo petty cash
    if ($outcome <= getSaldoPettyCash())
    { 
        // check no so
        if (isRowExist("aktivasi_so", "no_so", $params->no_so))
        {
            // check no bukti
            if (!isRowExist("aktivasi_so_biaya_lain", "no_bukti", $params->no_bukti))
            {
                // check no bukti kuitansi
                if (!isRowExist("aktivasi_so_jasa", "no_kuitansi", $params->no_kuitansi) || strlen($params->no_kuitansi) == 0)
                {
                    // check kode biaya
                    if (isRowExist("referensi_biaya", "kode", $params->kode_biaya))
                    {
                        // no so di wip kuitansi
                        // if (isRowExist("wip_aktivasi", "so_no", $params->no_so))
                        // {
                        $rollback = false;
                        $pdo->con->beginTransaction();
                        // insert aktivasi so biaya lain
                        $data = [
                            "no_bukti" => [$params->no_bukti, "string"],
                            "no_so" => [$params->no_so, "string"],
                            "tgl" => [$params->tgl, "string"],
                            "kode_biaya" => [$params->kode_biaya, "string"],
                            "jenis_biaya" => [$params->jenis_biaya, "string"],
                            "no_kuitansi" => [$params->no_kuitansi, "string"],
                            "tgl_kuitansi" => [$params->tgl_kuitansi, "string"],
                            "admin_kuitansi" => [$params->admin_kuitansi, "string"],
                            "penerima_kuitansi" => [$params->penerima_kuitansi, "string"],
                            "id_kuitansi" => [$params->id_kuitansi, "string"],
                            "admin_kuitansi" => [$params->admin_kuitansi, "string"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("aktivasi_so_biaya_lain", $data);

                        // insert petty cash
                        $data = [
                            "no_bukti" => [$params->no_bukti, "string"],
                            "tgl" => [$params->tgl, "string"],
                            "kode" => [$params->kode_biaya, "string"],
                            "jenis" => [$params->jenis_biaya, "string"],
                            "arus_kas" => ["KELUAR", "string"],
                            "tipe" => ["PENGELUARAN-AKTIVASI-SO-BIAYA-LAIN", "string"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $pdo->insert("petty_cash", $data);

                        // kurangi saldo petty cash
                        $sql = "UPDATE petty_cash_saldo SET saldo = saldo - :pengeluaran";
                        $pdo->sth = $pdo->con->prepare($sql);
                        $pdo->sth->bindParam(":pengeluaran", $outcome, PDO::PARAM_INT);
                        $pdo->execute();

                        // insert detail aktivasi rutin deskripsi transaksi
                        $totalBiayaLainSO = 0;
                        foreach ($params->detail as $idx => $desk) 
                        {
                            if (strlen($desk->deskripsi) == 0 || strlen($desk->nilai) == 0)
                            {
                                $rollback = true;
                                break;
                            }

                            $data = [
                                "no_bukti" => [$params->no_bukti, "string"],
                                "deskripsi" => [$desk->deskripsi, "string"],
                                "nilai" => [$desk->nilai, "int"],
                                "created_by" => [$g->logged->username, "string"]
                            ];
                            $pdo->insert("aktivasi_so_biaya_lain_detail", $data);

                            // insert into petty_cash_detail
                            $pdo->insert("petty_cash_detail", $data);

                            $totalBiayaLainSO += (int) str_replace(".", "", $desk->nilai);
                        }

                        if (!$rollback)
                        {
                            updateAutonumber("AKTIVASI-SO-BIAYA-LAIN", $g, $pdo);

                            // update auto number no kuitansi jika ada
                            if (strlen($params->no_kuitansi) > 10)
                                updateAutonumber("KUITANSI-AKTIVASI-SO-BIAYA-LAIN", $g, $pdo);

                            // update nilai jasa so
                            $sql = "UPDATE aktivasi_so SET nilai_biaya_lain = nilai_biaya_lain + :nilai_biaya_lain, modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE no_so = :no_so";
                            $pdo->sth = $pdo->con->prepare($sql);
                            $pdo->sth->bindParam(":nilai_biaya_lain", $totalBiayaLainSO, PDO::PARAM_INT);
                            $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                            $pdo->execute();

                            // update nilai so jasa di wip aktivasi
                            // $sql = "UPDATE wip_aktivasi SET so_biaya_lain_total = so_biaya_lain_total + " . $totalBiayaLainSO . ",  
                            //     modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE so_no = :no_so";
                            // $pdo->sth = $pdo->con->prepare($sql);
                            // $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                            // $pdo->execute();

                            $pdo->con->commit();

                            setResponseStatus(true, "Aktivasi SO biaya lain berhasil dibuat!");
                        }
                        else
                        {
                            $pdo->con->rollBack();

                            setResponseStatus(false, "Aktivasi SO biaya lain detail tidak boleh kosong!");
                        }
                        // }
                        // else
                        //     setResponseStatus(false, "No so tidak ditemukan pada wip aktivasi!");
                    }
                    else
                        setResponseStatus(false, "No kuitansi sudah digunakan!");
                }
                else
                    setResponseStatus(false, "Kode biaya " . $params->kode_biaya . " tidak ditemukan!");
            }
            else
                setResponseStatus(false, "Aktivasi SO biaya lain no bukti sudah digunakan!");
        }
        else
            setResponseStatus(false, "Aktivasi SO no so tidak ditemukan!");
    }
    else
        setResponseStatus(false, "Saldo petty cash tidak mencukupi, lakukan topup!");
});

Flight::route("POST|PUT /aktivasi/so/biaya_lain/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) AND (created_at BETWEEN '".$from_date."' AND '".$to_date."')";
    
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
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE (no_bukti LIKE CONCAT('%', :key, '%') OR tgl LIKE CONCAT('%', :key, '%') OR no_so LIKE CONCAT('%', :key, '%')) 
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

        $sql = "SELECT * FROM aktivasi_so_biaya_lain_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO Biaya Lain(s) retrieved!");
});

Flight::route("POST|PUT /aktivasi/so/biaya_lain/search_by/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {
    
    $key = $params->key;
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
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%')";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') AND created_at <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_bukti) as total_data FROM aktivasi_so_biaya_lain 
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
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
            AND created_at >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
            AND created_at <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so_biaya_lain 
            WHERE ".$params->search_by." LIKE CONCAT('%', :key, '%') 
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

        $sql = "SELECT * FROM aktivasi_so_biaya_lain_detail WHERE no_bukti = :no_bukti";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam("no_bukti", $row["no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["detail"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO Biaya Lain(s) retrieved!");
});

// AKTIVASI SO PKB: UI 100%
Flight::route("POST|PUT /aktivasi/so/pkb/create", function() use ($g, $pdo, $params) {
    // check aktivasi so
    if (isRowExist("aktivasi_so", "no_so", $params->no_so))
    {
        // check if pkb already inserted
        $sql = "SELECT pkb_no_bukti FROM aktivasi_so WHERE no_so = :no_so";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
        $pdo->execute();

        $so = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (strlen($so->pkb_no_bukti) == 0)
        {
            // check if no_bukti_pkb already inserted
            if (!isRowExist("aktivasi_so", "pkb_no_bukti", $params->pkb_no_bukti))
            {
                // no so di wip kuitansi
                if (isRowExist("wip_aktivasi", "so_no", $params->no_so))
                {
                    $rollback = false;
                    $rollbackMessage = "";
                    $pdo->con->beginTransaction();
                    $nilaiPKB = 0;
                    $totalQtyMaterial = 0;

                    // update so pkb
                    $sql = "UPDATE aktivasi_so SET pkb_no_bukti = :no_pkb, pkb_pelaksana = :pelaksana, 
                        pkb_tgl = :tgl, modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE no_so = :no_so";
                    $pdo->sth = $pdo->con->prepare($sql);
                    $pdo->sth->bindParam(":no_pkb", $params->pkb_no_bukti, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":pelaksana", $params->pkb_pelaksana, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":tgl", $params->pkb_tgl, PDO::PARAM_STR);
                    $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                    $pdo->execute();

                    if ($pdo->sth->rowCount() > 0)
                    {
                        // insert item
                        foreach ($params->item as $id => $item)
                        {
                            if (strlen($item->kode_khs) == 0 || strlen($item->jenis_khs) == 0 || strlen($item->qty) == 0 || strlen($item->total) == 0)
                            {
                                $rollback = true;
                                $rollbackMessage = "Detail Khs tidak boleh kosong!";
                                break;
                            }

                            // check each khs code
                            if (!isRowExist("referensi_khs", "kode", $item->kode_khs))
                            {
                                $rollback = true;
                                $rollbackMessage = "Kode khs " . $item->kode_khs . " tidak ditemukan!";
                                break;
                            }

                            // check quantity
                            if ((int) $item->qty == 0)
                            {
                                $rollback = true;
                                $rollbackMessage = "Qty khs ".$item->kode_khs." tidak boleh nol!";
                                break;
                            }

                            // check total q * harga != 0
                            if ((int) $item->total == 0)
                            {
                                $rollback = true;
                                $rollbackMessage = "Total khs ".$item->kode_khs." tidak boleh nol!";
                                break;
                            }

                            $data = [
                                "kode_khs" => [$item->kode_khs, "string"],
                                "jenis_khs" => [$item->jenis_khs, "string"],
                                "no_bukti" => [$params->pkb_no_bukti, "string"],
                                "qty" => [$item->qty, "int"],
                                "total" => [$item->total, "int"],
                                "created_by" => [$g->logged->username, "string"]
                            ];
                            $nilaiPKB += (int) $item->total;
                            $pdo->insert("aktivasi_so_pkb_item", $data);
                        }
                        
                        if (!$rollback)
                        { 
                            // insert material
                            foreach ($params->material as $id => $material)
                            {
                                if (strlen($material->kode_material) == 0 || strlen($material->jenis_material) == 0 || strlen($material->qty) == 0)
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
                                    "no_bukti" => [$params->pkb_no_bukti, "string"],
                                    "qty" => [$material->qty, "int"],
                                    "created_by" => [$g->logged->username, "string"]
                                ];
                                $pdo->insert("aktivasi_so_pkb_material", $data);

                                $totalQtyMaterial += (int) $material->qty;
                            }

                            if (!$rollback)
                            {
                                // update nilai pkb on aktivasi so
                                $sql = "UPDATE aktivasi_so SET pkb_nilai = :pkb_nilai, modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE no_so = :no_so";
                                $pdo->sth = $pdo->con->prepare($sql);
                                $pdo->sth->bindParam(":pkb_nilai", $nilaiPKB, PDO::PARAM_STR);
                                $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                                $pdo->execute();

                                // update pkb autonumber
                                updateAutonumber("AKTIVASI-SO-PKB", $g, $pdo);

                                // update so total material pada wip aktivasi
                                $sql = "UPDATE wip_aktivasi SET so_material_total = so_material_total + " . $totalQtyMaterial . ", 
                                    modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE so_no = :no_so";
                                $pdo->sth = $pdo->con->prepare($sql);
                                $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                                $pdo->execute();

                                $pdo->con->commit();
                                setResponseStatus(true, "Aktivasi SO PKB berhasil dibuat!");
                            }
                            else
                            {
                                $pdo->con->rollBack();
                                setResponseStatus(false, $rollbackMessage);
                            }
                        }
                        else
                        {
                            $pdo->con->rollBack();
                            setResponseStatus(false, $rollbackMessage);
                        }
                    }
                    else
                    {
                        $pdo->con->rollBack();
                        setResponseStatus(false, "Update PKB gagal, kegagalan eksekusi query!");
                    }
                }
                else
                    setResponseStatus(false, "No so tidak ditemukan pada wip aktivasi!");
            }
            else
                setResponseStatus(false, "No bukti PKB sudah digunakan!");
        }
        else
            setResponseStatus(false, "PKB telah diinput sebelumnya!");
    }
    else
        setResponseStatus(false, "No so tidak ditemukan!");
});

Flight::route("POST|PUT /aktivasi/so/pkb/edit", function() use ($g, $pdo, $params) {
    // check pkb
    if (isRowExist("aktivasi_so", "pkb_no_bukti", $params->pkb_no_bukti))
    {
        // get detail khs & material
        // $sql = "SELECT * FROM aktivasi_so_pkb_item WHERE no_bukti = :no_pkb";
        // $pdo->sth = $pdo->con->prepare($sql);
        // $pdo->sth->bindParam(":no_bukti", $params->pkb_no_bukti, PDO::PARAM_STR);
        // $pdo->execute();
        // $khs = $pdo->stmt->fetchAll();
        
        // $sql = "SELECT * FROM aktivasi_so_pkb_material WHERE no_bukti = :no_pkb";
        // $pdo->sth = $pdo->con->prepare($sql);
        // $pdo->sth->bindParam(":no_bukti", $params->pkb_no_bukti, PDO::PARAM_STR);
        // $pdo->execute();
        // $material = $pdo->stmt->fetchAll();

        // get no_so
        $sql = "SELECT no_so FROM aktivasi_so WHERE pkb_no_bukti = :pkb_no_bukti LIMIT 1";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":pkb_no_bukti", $params->pkb_no_bukti, PDO::PARAM_STR);
        $pdo->execute();

        $so = $pdo->sth->fetch(PDO::FETCH_OBJ);

        $rollback = false;
        $rollbackMessage = "";
        $nilaiPKB = 0;
        $totalQtyMaterial = 0;
        $pdo->con->beginTransaction();

        // delete pkb khs & item
        $sql = "DELETE FROM aktivasi_so_pkb_item WHERE no_bukti = :no_bukti";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":no_bukti", $params->pkb_no_bukti, PDO::PARAM_STR);
        $pdo->execute();

        $sql = "DELETE FROM aktivasi_so_pkb_material WHERE no_bukti = :no_bukti";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":no_bukti", $params->pkb_no_bukti, PDO::PARAM_STR);
        $pdo->execute();

        // insert item
        foreach ($params->item as $id => $item)
        {
            if (strlen($item->kode_khs) == 0 || strlen($item->jenis_khs) == 0 || strlen($item->qty) == 0 || strlen($item->total) == 0)
            {
                $rollback = true;
                $rollbackMessage = "Detail Khs tidak boleh kosong!";
                break;
            }

            // check each khs code
            if (!isRowExist("referensi_khs", "kode", $item->kode_khs))
            {
                $rollback = true;
                $rollbackMessage = "Kode khs " . $item->kode_khs . " tidak ditemukan!";
                break;
            }

            // check quantity
            if ((int) $item->qty == 0)
            {
                $rollback = true;
                $rollbackMessage = "Qty khs ".$item->kode_khs." tidak boleh nol!";
                break;
            }

            // check total q * harga != 0
            if ((int) $item->total == 0)
            {
                $rollback = true;
                $rollbackMessage = "Total khs ".$item->kode_khs." tidak boleh nol!";
                break;
            }

            $data = [
                "kode_khs" => [$item->kode_khs, "string"],
                "jenis_khs" => [$item->jenis_khs, "string"],
                "no_bukti" => [$params->pkb_no_bukti, "string"],
                "qty" => [$item->qty, "int"],
                "total" => [$item->total, "int"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $nilaiPKB += (int) $item->total;
            $pdo->insert("aktivasi_so_pkb_item", $data);
        }

        if (!$rollback)
        { 
            // insert material
            foreach ($params->material as $id => $material)
            {
                if (strlen($material->kode_material) == 0 || strlen($material->jenis_material) == 0 || strlen($material->qty) == 0)
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
                    "no_bukti" => [$params->pkb_no_bukti, "string"],
                    "qty" => [$material->qty, "int"],
                    "created_by" => [$g->logged->username, "string"]
                ];
                $pdo->insert("aktivasi_so_pkb_material", $data);

                $totalQtyMaterial += (int) $material->qty;
            }

            if (!$rollback)
            {
                // update nilai pkb on aktivasi so
                $sql = "UPDATE aktivasi_so SET pkb_nilai = :pkb_nilai, modified_at = NOW(), modified_by = '" . $g->logged->username . "' 
                    WHERE pkb_no_bukti = :pkb_no_bukti";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":pkb_nilai", $nilaiPKB, PDO::PARAM_STR);
                $pdo->sth->bindParam(":pkb_no_bukti", $params->pkb_no_bukti, PDO::PARAM_STR);
                $pdo->execute();

                // update so total material pada wip aktivasi
                $sql = "UPDATE wip_aktivasi SET so_material_total = " . $totalQtyMaterial . ", 
                    modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE so_no = :no_so";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":no_so", $so->no_so, PDO::PARAM_STR);
                $pdo->execute();

                $pdo->con->commit();
                setResponseStatus(true, "Update aktivasi SO PKB berhasil! ");
            }
            else
            {
                $pdo->con->rollBack();
                setResponseStatus(false, $rollbackMessage);
            }
        }
        else
        {
            $pdo->con->rollBack();
            setResponseStatus(false, $rollbackMessage);
        }
    }
    else
        setResponseStatus(false, "PKB tidak ditemukan!");
});

Flight::route("POST|PUT /aktivasi/so/pkb/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
    //$sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so WHERE no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') OR pkb_no_bukti LIKE CONCAT('%', :key, '%')";

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != ''";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != '' AND pkb_tgl >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != '' AND pkb_tgl <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != '' AND (pkb_tgl BETWEEN '".$from_date."' AND '".$to_date."')";

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

    /*
    $sql = "SELECT no_so, tgl, ptl, pekerjaan, pkb_no_bukti, pkb_tgl, pkb_pelaksana, created_at FROM aktivasi_so 
            WHERE no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') 
            OR pkb_no_bukti LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    */

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') 
            OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != '' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') 
            OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != '' 
            AND pkb_tgl >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') 
            OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != '' 
            AND pkb_tgl <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') 
            OR pkb_no_bukti LIKE CONCAT('%', :key, '%')) AND pkb_no_bukti != '' 
            AND (pkb_tgl BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        $sql = "SELECT * FROM aktivasi_so_pkb_item WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["khs"][] = $rowDetail;

        $sql = "SELECT * FROM aktivasi_so_pkb_material WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO PKB(s) retrieved!");
});

// AKTIVASI SO WASPANG: UI 100%
Flight::route("POST|PUT /aktivasi/so/waspang/create", function() use ($g, $pdo, $params) {
    // search no_so
    $sql = "SELECT * FROM aktivasi_so WHERE no_so = :no_so";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
    $pdo->execute();

    $aktivasiSO = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($aktivasiSO)
    {
        // check if waspang already exist
        if (strlen((string) $aktivasiSO->waspang_no_ba) == 0)
        {
            // check if no ba waspang already exist
            if (!isRowExist("aktivasi_so", "waspang_no_ba", $params->waspang_no_ba))
            {
                $sql = "UPDATE aktivasi_so SET waspang_no_ba = :no_ba, waspang_tgl = :tgl, waspang_nama = :nama, 
                    modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE no_so = :no_so";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":no_ba", $params->waspang_no_ba, PDO::PARAM_STR);
                $pdo->sth->bindParam(":tgl", $params->waspang_tgl, PDO::PARAM_STR);
                $pdo->sth->bindParam(":nama", $params->waspang_nama, PDO::PARAM_STR);
                $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                $pdo->execute();

                if ($pdo->sth->rowCount() > 0)
                {
                    updateAutonumber("AKTIVASI-SO-BA-WASPANG", $g, $pdo);
                    setResponseStatus(true, "Aktivasi SO Waspang berhasil dibuat!");
                }
                else
                    setResponseStatus(false, "No data inserted!");
            }
            else
                setResponseStatus(false, "No. BA Waspang sudah ada sebelumnya!");
        }
        else
            setResponseStatus(false, "No. BA Waspang sudah dibuat sebelumnya!");
    }
    else
        setResponseStatus(false, "Aktivasi SO no so tidak ditemukan!");
});

Flight::route("POST|PUT /aktivasi/so/waspang/search/@page/@limit", function($page, $limit) use ($g, $pdo, $params) {
    
    $key = $params->key;
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
    //$sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so WHERE no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') OR pkb_no_bukti LIKE CONCAT('%', :key, '%')";

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != ''";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != '' AND waspang_tgl >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != '' AND waspang_tgl <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != '' AND (waspang_tgl BETWEEN '".$from_date."' AND '".$to_date."')";

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

    /*
    $sql = "SELECT no_so, tgl, ptl, pekerjaan, pkb_no_bukti, pkb_tgl, pkb_pelaksana, created_at FROM aktivasi_so 
            WHERE no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') 
            OR pkb_no_bukti LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    */

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') 
            OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != '' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') 
            OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != '' 
            AND waspang_tgl >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') 
            OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != '' 
            AND waspang_tgl <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR waspang_tgl LIKE CONCAT('%', :key, '%') 
            OR waspang_no_ba LIKE CONCAT('%', :key, '%')) AND waspang_no_ba != '' 
            AND (waspang_tgl BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        $sql = "SELECT * FROM aktivasi_so_pkb_item WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["khs"][] = $rowDetail;

        $sql = "SELECT * FROM aktivasi_so_pkb_material WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material"][] = $rowDetail;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO Waspang(s) retrieved!");
});

// AKTIVASI SO PKT
Flight::route("POST|PUT /aktivasi/so/pkt/create", function() use ($g, $pdo, $params) {
    // check aktivasi so
    $sql = "SELECT * FROM aktivasi_so WHERE no_so = :no_so";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
    $pdo->execute();

    $so = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($so)
    {
        // check if pkb already inserted
        if (strlen($so->pkt_no_bukti) == 0)
        {
            // check if no_bukti_pkb already inserted
            if (!isRowExist("aktivasi_so", "pkt_no_bukti", $params->pkt_no_bukti))
            {
                $rollback = false;
                $rollbackMessage = "";
                $pdo->con->beginTransaction();
                $nilaiPKT = 0;

                // update so pkb
                $sql = "UPDATE aktivasi_so SET pkt_no_bukti = :no_pkt, 
                    pkt_tgl = :tgl, modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE no_so = :no_so";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":no_pkt", $params->pkt_no_bukti, PDO::PARAM_STR);
                $pdo->sth->bindParam(":tgl", $params->pkt_tgl, PDO::PARAM_STR);
                $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                $pdo->execute();

                if ($pdo->sth->rowCount() > 0)
                {
                    // insert item
                    foreach ($params->item as $id => $item)
                    {
                        // check detail
                        if (strlen($item->kode_khs) == 0 || strlen($item->jenis_khs) == 0 || strlen($item->qty) == 0 || strlen($item->total) == 0)
                        {
                            $rollback = true;
                            $rollbackMessage = "Detail khs tidak boleh kosong!";
                            break;
                        }

                        // check quantity
                        if ((int) $item->qty == 0)
                        {
                            $rollback = true;
                            $rollbackMessage = "Qty khs ".$item->kode_khs." tidak boleh nol!";
                            break;
                        }

                        // check total q * harga != 0
                        if ((int) $item->total == 0)
                        {
                            $rollback = true;
                            $rollbackMessage = "Total khs ".$item->kode_khs." tidak boleh nol!";
                            break;
                        }

                        // check kode exist
                        if (!isRowExist("referensi_khs", "kode", $item->kode_khs))
                        {
                            $rollback = true;
                            $rollbackMessage = "Kode khs " . $item->kode_khs . " tidak ditemukan!";
                            break;
                        }

                        $data = [
                            "kode_khs" => [$item->kode_khs, "string"],
                            "jenis_khs" => [$item->jenis_khs, "string"],
                            "no_pkt" => [$params->pkt_no_bukti, "string"],
                            "qty" => [$item->qty, "int"],
                            "total" => [$item->total, "int"],
                            "created_by" => [$g->logged->username, "string"]
                        ];
                        $nilaiPKT += (int) $item->total;
                        $pdo->insert("aktivasi_so_pkt_item", $data);
                    }
                    
                    if (!$rollback)
                    { 
                        // insert material
                        foreach ($params->material as $id => $material)
                        {
                            if (strlen($material->kode_material) == 0 || strlen($material->jenis_material) == 0 || strlen($material->qty) == 0)
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

                            if (!isRowExist("referensi_material", "kode", $material->kode_material))
                            {
                                $rollback = true;
                                $rollbackMessage = "Kode material " . $material->kode_material . " tidak ditemukan!";
                                break;
                            }

                            $data = [
                                "kode_material" => [$material->kode_material, "string"],
                                "jenis_material" => [$material->jenis_material, "string"],
                                "no_pkt" => [$params->pkt_no_bukti, "string"],
                                "qty" => [$material->qty, "int"],
                                "created_by" => [$g->logged->username, "string"]
                            ];
                            $pdo->insert("aktivasi_so_pkt_material", $data);
                        }

                        if (!$rollback)
                        {
                            // update nilai pkb on aktivasi so
                            $sql = "UPDATE aktivasi_so SET pkt_nilai = :pkt_nilai, modified_at = NOW(), modified_by = '" . $g->logged->username . "' WHERE no_so = :no_so";
                            $pdo->sth = $pdo->con->prepare($sql);
                            $pdo->sth->bindParam(":pkt_nilai", $nilaiPKT, PDO::PARAM_STR);
                            $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
                            $pdo->execute();

                            $pdo->con->commit();
                            setResponseStatus(true, "Aktivasi SO PKT Inserted!");
                        }
                        else
                        {
                            $pdo->con->rollBack();
                            setResponseStatus(false, $rollbackMessage);
                        }
                    }
                    else
                    {
                        $pdo->con->rollBack();
                        setResponseStatus(false, $rollbackMessage);
                    }
                }
                else
                {
                    $pdo->con->rollBack();
                    setResponseStatus(false, "Update PKT gagal!");
                }
            }
            else
                setResponseStatus(false, "No bukti PKT sudah ada sebelumnya!");
        }
        else
            setResponseStatus(false, "PKT telah diinput sebelumnya!");
    }
    else
        setResponseStatus(false, "No so tidak ditemukan!");
});

Flight::route("POST|PUT /aktivasi/so/pkt/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    
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
    //$sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so WHERE no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') OR pkb_no_bukti LIKE CONCAT('%', :key, '%')";

    /*
    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != ''";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != '' AND pkt_tgl >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != '' AND pkt_tgl <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != '' AND (pkt_tgl BETWEEN '".$from_date."' AND '".$to_date."')";
    */

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%'))";
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_tgl >= '".$from_date."'";
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_tgl <= '".$to_date."'";
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT COUNT(no_so) as total_data FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND (pkt_tgl BETWEEN '".$from_date."' AND '".$to_date."')";


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

    /*
    $sql = "SELECT no_so, tgl, ptl, pekerjaan, pkb_no_bukti, pkb_tgl, pkb_pelaksana, created_at FROM aktivasi_so 
            WHERE no_so LIKE CONCAT('%', :key, '%') OR pkb_tgl LIKE CONCAT('%', :key, '%') 
            OR pkb_no_bukti LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    */

    /*
    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != '' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != '' 
            AND pkt_tgl >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != '' 
            AND pkt_tgl <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) AND pkt_no_bukti != '' 
            AND (pkt_tgl BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    */

    if ($from_date == 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%'))  
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date == 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%'))  
            AND pkt_tgl >= '".$from_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date == 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%'))  
            AND pkt_tgl <= '".$to_date."' 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else if ($from_date != 0 && $to_date != 0)
        $sql = "SELECT * FROM aktivasi_so 
            WHERE (no_so LIKE CONCAT('%', :key, '%') OR pkt_tgl LIKE CONCAT('%', :key, '%') 
            OR pkt_no_bukti LIKE CONCAT('%', :key, '%')) 
            AND (pkt_tgl BETWEEN '".$from_date."' AND '".$to_date."') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $pdoChild = new MyPdo();

        // get pkb
        $sql = "SELECT * FROM aktivasi_so_pkb_item WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["khs"][] = $rowDetail;

        $sql = "SELECT * FROM aktivasi_so_pkb_material WHERE no_bukti = :no_bukti_pkb";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkb", $row["pkb_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material"][] = $rowDetail;

        // get pkt
        $sql = "SELECT * FROM aktivasi_so_pkt_item WHERE no_pkt = :no_bukti_pkt";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkt", $row["pkt_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["khs_pkt"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["khs_pkt"][] = $rowDetail;

        $sql = "SELECT * FROM aktivasi_so_pkt_material WHERE no_pkt = :no_bukti_pkt";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->sth->bindParam(":no_bukti_pkt", $row["pkt_no_bukti"], PDO::PARAM_STR);
        $pdoChild->execute();

        $row["material_pkt"] = [];
        while ($rowDetail = $pdoChild->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            $row["material_pkt"][] = $rowDetail;


        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Aktivasi SO PKT(s) retrieved!");
});

Flight::route("POST|PUT /aktivasi/so/pkt/is_pkb_rugi", function() use ($g, $pdo, $params) {

    if (isRowExist("aktivasi_so", "pkb_no_bukti", $params->no_pkb))
    {
        // get list material dari pkb
        $sql = "SELECT * FROM aktivasi_so_pkb_material WHERE no_bukti = :no_bukti";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":no_bukti", $params->no_pkb);
        $pdo->execute();

        $kerugian = false;
        $message = "PKB tidak mengalami kerugian";
        $g->response["result"]["data"] = [];
        while ($row = $pdo->sth->fetch(PDO::FETCH_OBJ, PDO::FETCH_ORI_NEXT))
        {
            $pdoChild = new MyPdo();
            
            // get qty out material pada pkt
            $sql = "SELECT SUM(mpd.qty_out) AS total_qty_out FROM material_pengeluaran AS mp 
                JOIN material_pengeluaran_detail AS mpd ON mp.no_bukti = mpd.no_bukti 
                WHERE mp.no_dokumen = :no_pkb AND mp.type = 'Q-OUT' AND mpd.kode_material = '".$row->kode_material."'";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->sth->bindParam(":no_pkb", $params->no_pkb, PDO::PARAM_STR);
            $pdoChild->execute();

            $rowQtyOut = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

            // get qty retur material pada pkt
            $sql = "SELECT SUM(mpd.retur) AS total_qty_retur FROM material_pengeluaran AS mp 
                JOIN material_pengeluaran_detail AS mpd ON mp.no_bukti = mpd.no_bukti 
                WHERE mp.no_dokumen = :no_pkb AND mp.type = 'RETUR' AND mpd.kode_material = '".$row->kode_material."'";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->sth->bindParam(":no_pkb", $params->no_pkb, PDO::PARAM_STR);
            $pdoChild->execute();

            $rowRetur = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

            // get qty loss material pada pkt
            $sql = "SELECT SUM(mpd.loss) AS total_qty_loss FROM material_pengeluaran AS mp 
                JOIN material_pengeluaran_detail AS mpd ON mp.no_bukti = mpd.no_bukti 
                WHERE mp.no_dokumen = :no_pkb AND mp.type = 'KERUGIAN' AND mpd.kode_material = '".$row->kode_material."'";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->sth->bindParam(":no_pkb", $params->no_pkb, PDO::PARAM_STR);
            $pdoChild->execute();

            $rowLoss = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

            $qtyPkbImplement = (int) $rowQtyOut->total_qty_out - (int) $rowRetur->total_qty_retur - (int) $rowLoss->total_qty_loss;

            // get qty ketika pkt
            $sql = "SELECT SUM(a.qty) AS total_qty_pkt FROM aktivasi_so_pkt_material AS a JOIN aktivasi_so AS b 
                ON a.no_pkt = b.pkt_no_bukti WHERE b.pkb_no_bukti = :no_pkb AND a.kode_material = '".$row->kode_material."'";
            $pdoChild->sth = $pdoChild->con->prepare($sql);
            $pdoChild->sth->bindParam(":no_pkb", $params->no_pkb, PDO::PARAM_STR);
            $pdoChild->execute();
            $rowPkt = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

            if ($rowPkt->total_qty_pkt < $qtyPkbImplement)
            {
                $kerugian = true;
                $message = "PKB mengalami kerugian";
                $g->response["result"]["data"][] = [
                    "kode_material" => $row->kode_material,
                    "jenis_material" => $row->jenis_material,
                    "qty_loss" => $qtyPkbImplement - $rowPkt->total_qty_pkt
                ];
            }
        }
        $g->response["result"]["kerugian"] = $kerugian;
            
        setResponseStatus(true, $message);
    }
    else
        setResponseStatus(false, "No. PKB tidak ditemukan");
});

Flight::route("POST|PUT /aktivasi/so/pkt/upload_gr", function() use ($g, $pdo, $params) {

    if (isRowExist("aktivasi_so", "pkt_no_bukti", $params->pkt_no_bukti))
    {
        $pdo->con->beginTransaction();
        // update file gr pkt
        $sql = "UPDATE aktivasi_so SET pkt_gr_file = :gr_file, pkt_gr_tgl = NOW(), 
            modified_at = NOW(), modified_by = '".$g->logged->username."' WHERE pkt_no_bukti = :no_pkt";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":gr_file", $params->pkt_gr_file, PDO::PARAM_STR);
        $pdo->sth->bindParam(":no_pkt", $params->pkt_no_bukti, PDO::PARAM_STR);
        $pdo->execute();

        // update file gr di wip aktivasi
        $sql = "UPDATE wip_aktivasi SET good_receive_file = :gr_file, 
            modified_at = NOW(), modified_by = '".$g->logged->username."' WHERE so_no = :no_so";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":gr_file", $params->pkt_gr_file, PDO::PARAM_STR);
        $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
        $pdo->execute();

        $pdo->con->commit();

        setResponseStatus(true, "Update file GR PKT dan WIP Aktivasi berhasil!");
    }
    else
        setResponseStatus(false, "No bukti PKT tidak ditemukan!");
});

Flight::route("POST|PUT /aktivasi/so/pkt/hapus_gr", function() use ($g, $pdo, $params) {

    if (isRowExist("aktivasi_so", "no_so", $params->no_so))
    {
        $pdo->con->beginTransaction();
        // update file gr pkt
        $sql = "UPDATE aktivasi_so SET pkt_gr_file = NULL, 
            modified_at = NOW(), modified_by = '".$g->logged->username."' WHERE no_so = :no_so";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
        $pdo->execute();

        // update file gr di wip aktivasi
        $sql = "UPDATE wip_aktivasi SET good_receive_file = NULL, 
            modified_at = NOW(), modified_by = '".$g->logged->username."' WHERE so_no = :no_so";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":no_so", $params->no_so, PDO::PARAM_STR);
        $pdo->execute();

        $pdo->con->commit();

        setResponseStatus(true, "Hapus file GR PKT dan WIP Aktivasi berhasil!");
    }
    else
        setResponseStatus(false, "No So tidak ditemukan!");
});