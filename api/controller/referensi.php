<?php

// MASTER ITEM PKB
Flight::route("POST|PUT /master/item/add", function() use ($g, $pdo, $params) {
    
    // check existing kode
    $sql = "SELECT kode FROM master_item_pkb WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $params->kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isCodeExist)
    {
        // check existing name
        $nama = strtolower(trim($params->nama));
        $sql = "SELECT kode FROM master_item_pkb WHERE LOWER(nama) = :nama";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":nama", $nama, PDO::PARAM_STR);
        $pdo->execute();

        $isNameExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNameExist)
        {
            $data = array(
                "kode" => [$params->kode, "string"],
                "nama" => [trim($params->nama), "string"],
                "harga" => [$params->harga, "int"],
                "created_by" => [$g->logged->username, "string"]
            );
            $pdo->insert("master_item_pkb", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "nama" => $params->nama,
                "harga" => $params->harga
            ];
            setResponseStatus(true, "New master item inserted!");
        }
        else
            setResponseStatus(false, "Master item name already in use!");
    }
    else
        setResponseStatus(false, "Master item code already in use!");
});

Flight::route("POST|PUT /master/item/edit/@kode", function($kode) use ($g, $pdo, $params) {

    // check if kode exist
    $sql = "SELECT kode FROM master_item_pkb WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isCodeExist)
    {
        // check if new kode available
        $sql = "SELECT kode FROM master_item_pkb WHERE kode = :new_code AND kode != :old_code";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewCodeExist)
        {
            // check if new item name available
            $nama = strtolower($params->nama);
            $sql = "SELECT kode FROM master_item_pkb WHERE LOWER(nama) = :nama AND kode != :old_code";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":nama", $nama, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $isNewNameExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isNewNameExist) 
            {
                $sql = "UPDATE master_item_pkb SET kode = :new_code, nama = :nama, harga = :harga,
                    modified_by = :modified_by, modified_at = NOW() WHERE kode = :old_code";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
                $pdo->sth->bindParam(":nama", $params->nama, PDO::PARAM_STR);
                $pdo->sth->bindParam(":harga", $harga, PDO::PARAM_INT);
                $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                    setResponseStatus(true, "Update master item success!");
                else
                    setResponseStatus(false, "No data changed!");
            }
            else
                setResponseStatus(false, "Master item name already in use!");
        }
        else
            setResponseStatus(false, "Master item kode already in use!");
    }
    else
        setResponseStatus(false, "Master item not found!");
});

Flight::route("POST|PUT /master/item/delete/@kode", function($kode) use ($g, $pdo, $params) {

    $sql = "DELETE FROM master_item_pkb WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Master item deleted!"); 
    else
        setResponseStatus(false, "Delete failed or item code not found!"); 
});

Flight::route("POST|PUT /master/item/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM master_item_pkb 
        WHERE kode LIKE CONCAT('%', :key, '%') OR nama LIKE CONCAT('%', :key, '%')";
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

    $sql = "SELECT kode, nama, harga, created_at FROM master_item_pkb 
            WHERE kode LIKE CONCAT('%', :key, '%') OR nama LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Master item(s) retrieved!");
});

// MASTER MATERIAL
Flight::route("POST|PUT /master/material/add", function() use ($g, $pdo, $params) {
    
    // check existing kode
    $sql = "SELECT kode FROM master_material WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $params->kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isCodeExist)
    {
        // check existing jenis
        $jenis = strtolower(trim($params->jenis));
        $sql = "SELECT kode FROM master_material WHERE LOWER(jenis) = :jenis";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
        $pdo->execute();

        $isJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isJenisExist)
        {
            $data = array(
                "kode" => [$params->kode, "string"],
                "jenis" => [trim(strtoupper($params->jenis)), "string"],
                "satuan" => [$params->satuan, "string"],
                "harga" => [$params->harga, "int"],
                "qty" => [$params->qty, "int"],
                "created_by" => [$g->logged->username, "string"]
            );
            $pdo->insert("master_material", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "jenis" => $params->jenis,
                "harga" => $params->harga,
                "qty" => $params->qty
            ];
            setResponseStatus(true, "New master material inserted!");
        }
        else
            setResponseStatus(false, "Master material jenis already in use!");
    }
    else
        setResponseStatus(false, "Master material code already in use!");
});

Flight::route("POST|PUT /master/material/edit/@kode", function($kode) use ($g, $pdo, $params) {

    // check if kode exist
    $sql = "SELECT kode FROM master_material WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isCodeExist)
    {
        // check if new kode available
        $sql = "SELECT kode FROM master_material WHERE kode = :new_code AND kode != :old_code";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewCodeExist)
        {
            // check if new jenis available
            $jenis = strtolower($params->jenis);
            $sql = "SELECT kode FROM master_material WHERE LOWER(jenis) = :jenis AND kode != :old_code";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $isNewJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isNewJenisExist) 
            {
                $sql = "UPDATE master_material SET kode = :new_code, jenis = :jenis, harga = :harga,
                    modified_by = :modified_by, modified_at = NOW(), satuan = :satuan, qty = :qty WHERE kode = :old_code";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
                $pdo->sth->bindParam(":jenis", $params->jenis, PDO::PARAM_STR);
                $pdo->sth->bindParam(":harga", $params->harga, PDO::PARAM_INT);
                $pdo->sth->bindParam(":satuan", $params->satuan, PDO::PARAM_STR);
                $pdo->sth->bindParam(":qty", $params->qty, PDO::PARAM_INT);
                $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                    setResponseStatus(true, "Update master material success!");
                else
                    setResponseStatus(false, "No data changed!");
            }
            else
                setResponseStatus(false, "Master material jenis already in use!");
        }
        else
            setResponseStatus(false, "Master material kode already in use!");
    }
    else
        setResponseStatus(false, "Master material not found!");
});

Flight::route("POST|PUT /master/material/delete/@kode", function($kode) use ($g, $pdo, $params) {

    $sql = "DELETE FROM master_material WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Master material deleted!"); 
    else
        setResponseStatus(false, "Delete failed or material code not found!"); 
});

Flight::route("POST|PUT /master/material/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM master_material 
        WHERE kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%')";
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

    $sql = "SELECT kode, jenis, harga, satuan, qty created_at FROM master_material 
            WHERE kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Master material(s) retrieved!");
});

// REFERENSI JASA: UI 100%
Flight::route("POST|PUT /referensi/jasa/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "kode, jenis, harga, token");
    // check existing kode
    $params->kode = strtoupper(trim($params->kode));
    $sql = "SELECT kode FROM referensi_jasa WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $params->kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isCodeExist)
    {
        // check existing jenis
        $jenis = strtoupper(trim($params->jenis));
        $sql = "SELECT kode FROM referensi_jasa WHERE UPPER(jenis) = :jenis";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
        $pdo->execute();

        $isJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isJenisExist)
        {
            $data = [
                "kode" => [$params->kode, "string"],
                "jenis" => [$jenis, "string"],
                "harga" => [$params->harga, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_jasa", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "jenis" => $jenis,
                "harga" => $params->harga
            ];
            setResponseStatus(true, "Referensi jasa berhasil ditambahkan!");
        }
        else
            setResponseStatus(false, "Jenis referensi jasa sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Kode referensi jasa sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/jasa/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_kode, jenis, harga, token");
    // check if kode exist
    $kode = strtoupper(trim($kode));
    $sql = "SELECT kode FROM referensi_jasa WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isCodeExist)
    {
        // check if new kode available
        $sql = "SELECT kode FROM referensi_jasa WHERE kode = :new_code AND kode != :old_code";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewCodeExist)
        {
            // check if new jenis available
            $jenis = trim(strtoupper($params->jenis));
            $sql = "SELECT kode FROM referensi_jasa WHERE UPPER(jenis) = :jenis AND kode != :old_code";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $isNewJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isNewJenisExist) 
            {
                $params->new_kode = trim(strtoupper($params->new_kode));
                $sql = "UPDATE referensi_jasa SET kode = :new_code, jenis = :jenis, harga = :harga,   
                    modified_by = :modified_by, modified_at = NOW() WHERE kode = :old_code";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
                $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
                $pdo->sth->bindParam(":harga", $params->harga, PDO::PARAM_INT);
                $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                {
                    $g->response["result"] = [
                        "kode" => $params->new_kode,
                        "jenis" => $jenis,
                        "harga" => $params->harga
                    ];
                    setResponseStatus(true, "Update referensi jasa berhasil!");
                }
                else
                    setResponseStatus(false, "Tidak ada perubahan data!");
            }
            else
                setResponseStatus(false, "Jenis referensi jasa sudah ada sebelumnya!");
        }
        else
            setResponseStatus(false, "Kode referensi jasa sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi jasa tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/jasa/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $kode = strtoupper(trim($kode));
    $sql = "DELETE FROM referensi_jasa WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi jasa " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi jasa " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/jasa/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM referensi_jasa 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT kode, jenis, harga, created_at FROM referensi_jasa
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi jasa ditemukan!");
});

// REFERENSI MATERIAL: UI 100%
Flight::route("POST|PUT /referensi/material/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "kode, jenis, satuan, harga, red_line, token");
    // check existing kode
    $params->kode = strtoupper(trim($params->kode));
    $sql = "SELECT kode FROM referensi_material WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $params->kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isCodeExist)
    {
        // check existing jenis
        $jenis = strtoupper(trim($params->jenis));
        $sql = "SELECT kode FROM referensi_material WHERE UPPER(jenis) = :jenis";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
        $pdo->execute();

        $isJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isJenisExist)
        {
            $params->satuan = strtoupper(trim($params->satuan));
            $params->red_line = strtoupper(trim($params->red_line));
            $data = [
                "kode" => [$params->kode, "string"],
                "jenis" => [$jenis, "string"],
                "satuan" => [$params->satuan, "string"],
                "harga" => [$params->harga, "string"],
                "red_line" => [$params->red_line, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_material", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "jenis" => $jenis,
                "satuan" => $params->satuan,
                "harga" => $params->harga,
                "red_line" => $params->red_line
            ];
            setResponseStatus(true, "Referensi material berhasil ditambahkan!");
        }
        else
            setResponseStatus(false, "Jenis referensi material sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Kode referensi material sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/material/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_kode, jenis, satuan, harga, red_line, token");
    // check if kode exist
    $kode = strtoupper(trim($kode));
    $sql = "SELECT kode FROM referensi_material WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isCodeExist)
    {
        // check if new kode available
        $sql = "SELECT kode FROM referensi_material WHERE kode = :new_code AND kode != :old_code";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewCodeExist)
        {
            // check if new jenis available
            $jenis = trim(strtoupper($params->jenis));
            $sql = "SELECT kode FROM referensi_material WHERE UPPER(jenis) = :jenis AND kode != :old_code";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $isNewJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isNewJenisExist) 
            {
                $params->new_kode = trim(strtoupper($params->new_kode));
                $params->satuan = strtoupper(trim($params->satuan));
                $params->red_line = strtoupper(trim($params->red_line));
                $sql = "UPDATE referensi_material SET kode = :new_code, jenis = :jenis,
                    harga = :harga, satuan = :satuan, red_line = :red_line,  
                    modified_by = :modified_by, modified_at = NOW() WHERE kode = :old_code";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
                $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
                $pdo->sth->bindParam(":harga", $params->harga, PDO::PARAM_STR);
                $pdo->sth->bindParam(":satuan", $params->satuan, PDO::PARAM_STR);
                $pdo->sth->bindParam(":red_line", $params->red_line, PDO::PARAM_STR);
                $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                {
                    $g->response["result"] = [
                        "kode" => $params->new_kode,
                        "jenis" => $jenis,
                        "harga" => $params->harga,
                        "satuan" => $params->satuan,
                        "red_line" => $params->red_line
                    ];
                    setResponseStatus(true, "Update referensi material berhasil!");
                }
                else
                    setResponseStatus(false, "Tidak ada perubahan data!");
            }
            else
                setResponseStatus(false, "Jenis referensi material sudah ada sebelumnya!");
        }
        else
            setResponseStatus(false, "Kode referensi material sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi material tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/material/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $kode = strtoupper(trim($kode));
    $sql = "DELETE FROM referensi_material WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi material " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi material " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/material/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM referensi_material 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT kode, jenis, satuan, harga, red_line, created_at FROM referensi_material 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi material ditemukan!");
});

// REFERENSI MATERIAL REGIONAL: UI 100%
Flight::route("POST|PUT /referensi/material/regional/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "kode, nama, token");
    // check existing kode
    if (!isRowExist("referensi_material_regional", "kode", $params->kode))
    {
        // check existing nama
        if (!isRowExist("referensi_material_regional", "nama", $params->nama))
        {
            $params->nama = trim(strtoupper($params->nama));
            $data = [
                "kode" => [$params->kode, "string"],
                "nama" => [$params->nama, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_material_regional", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "nama" => $params->nama,
            ];
            setResponseStatus(true, "Referensi material regional " . $params->kode . " (" . $params->nama . ") berhasil ditambahkan!");
        }
        else
            setResponseStatus(false, "Nama referensi material regional " . $params->nama . " sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Kode referensi material regional " . $params->kode . " sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/material/regional/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_kode, nama, token");
    // check if kode exist
    if (isRowExist("referensi_material_regional", "kode", $kode))
    {
        // check if new kode available
        $sql = "SELECT kode FROM referensi_material_regional WHERE kode = :new_code AND kode != :old_code";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewCodeExist)
        {
            // check if new jenis available
            $params->nama = trim(strtoupper($params->nama));
            $sql = "SELECT kode FROM referensi_material_regional WHERE UPPER(nama) = :nama AND kode != :old_code";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":nama", $params->nama, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $isNewNamaExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isNewNamaExist) 
            {
                $params->new_kode = trim(strtoupper($params->new_kode));
                $sql = "UPDATE referensi_material_regional SET kode = :new_code, nama = :nama,  
                    modified_by = :modified_by, modified_at = NOW() WHERE kode = :old_code";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
                $pdo->sth->bindParam(":nama", $params->nama, PDO::PARAM_STR);
                $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                {
                    $g->response["result"] = [
                        "kode" => $params->new_kode,
                        "nama" => $params->nama,
                    ];
                    setResponseStatus(true, "Update referensi material regional berhasil!");
                }
                else
                    setResponseStatus(false, "Tidak ada perubahan data!");
            }
            else
                setResponseStatus(false, "Nama referensi material regional " . $params->nama . " sudah ada sebelumnya!");
        }
        else
            setResponseStatus(false, "Kode referensi material regional " . $kode . " sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi material regional " . $kode . " tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/material/regional/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $kode = strtoupper(trim($kode));
    $sql = "DELETE FROM referensi_material_regional WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi material regional " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi material regional " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/material/regional/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM referensi_material_regional 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR nama LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT kode, nama, created_at FROM referensi_material_regional 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR nama LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi material regional ditemukan!");
});

// REFERENSI REGIONAL: UI 100%
Flight::route("POST|PUT /referensi/regional/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "kode, wilayah, token");
    // check existing kode
    $params->kode = strtoupper(trim($params->kode));
    $sql = "SELECT kode FROM referensi_regional WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $params->kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isCodeExist)
    {
        // check existing jenis
        $wilayah = strtoupper(trim($params->wilayah));
        $sql = "SELECT kode FROM referensi_regional WHERE UPPER(wilayah) = :wilayah";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":wilayah", $wilayah, PDO::PARAM_STR);
        $pdo->execute();

        $isWilayahExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isWilayahExist)
        {
            $data = [
                "kode" => [$params->kode, "string"],
                "wilayah" => [$wilayah, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_regional", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "wilayah" => $wilayah,
            ];
            setResponseStatus(true, "Referensi regional berhasil ditambahkan!");
        }
        else
            setResponseStatus(false, "Wilayah referensi regional sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Kode referensi regional sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/regional/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_kode, wilayah, token");
    // check if kode exist
    $kode = strtoupper(trim($kode));
    $sql = "SELECT kode FROM referensi_regional WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isCodeExist)
    {
        // check if new kode available
        $sql = "SELECT kode FROM referensi_regional WHERE kode = :new_code AND kode != :old_code";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewCodeExist)
        {
            // check if new jenis available
            $wilayah = trim(strtoupper($params->wilayah));
            $sql = "SELECT kode FROM referensi_regional WHERE UPPER(wilayah) = :wilayah AND kode != :old_code";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":wilayah", $wilayah, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $isNewJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isNewJenisExist) 
            {
                $params->new_kode = trim(strtoupper($params->new_kode));
                $sql = "UPDATE referensi_regional SET kode = :new_code, wilayah = :wilayah,  
                    modified_by = :modified_by, modified_at = NOW() WHERE kode = :old_code";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
                $pdo->sth->bindParam(":wilayah", $wilayah, PDO::PARAM_STR);
                $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                {
                    $g->response["result"] = [
                        "kode" => $params->new_kode,
                        "wilayah" => $wilayah,
                    ];
                    setResponseStatus(true, "Update referensi regional berhasil!");
                }
                else
                    setResponseStatus(false, "Tidak ada perubahan data!");
            }
            else
                setResponseStatus(false, "Wilayah referensi regional sudah ada sebelumnya!");
        }
        else
            setResponseStatus(false, "Kode referensi regional sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi regional tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/regional/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $kode = strtoupper(trim($kode));
    $sql = "DELETE FROM referensi_regional WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi regional " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi regional " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/regional/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM referensi_regional 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR wilayah LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT kode, wilayah, created_at FROM referensi_regional 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR wilayah LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi regional ditemukan!");
});

// REFERENSI BIAYA: UI 100%
Flight::route("POST|PUT /referensi/biaya/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "kode, jenis, token");
    // check existing kode
    $params->kode = strtoupper(trim($params->kode));
    $sql = "SELECT kode FROM referensi_biaya WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $params->kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isCodeExist)
    {
        // check existing jenis
        $jenis = strtoupper(trim($params->jenis));
        $sql = "SELECT kode FROM referensi_biaya WHERE UPPER(jenis) = :jenis";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
        $pdo->execute();

        $isJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isJenisExist)
        {
            $data = [
                "kode" => [$params->kode, "string"],
                "jenis" => [$jenis, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_biaya", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "jenis" => $jenis,
            ];
            setResponseStatus(true, "Referensi biaya berhasil ditambahkan!");
        }
        else
            setResponseStatus(false, "Jenis referensi biaya sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Kode referensi biaya sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/biaya/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_kode, jenis, token");
    // check if kode exist
    $kode = strtoupper(trim($kode));
    $sql = "SELECT kode FROM referensi_biaya WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isCodeExist)
    {
        // check if new kode available
        $sql = "SELECT kode FROM referensi_biaya WHERE kode = :new_code AND kode != :old_code";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewCodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewCodeExist)
        {
            // check if new jenis available
            $jenis = trim(strtoupper($params->jenis));
            $sql = "SELECT kode FROM referensi_biaya WHERE UPPER(jenis) = :jenis AND kode != :old_code";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $isNewJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isNewJenisExist) 
            {
                $params->new_kode = trim(strtoupper($params->new_kode));
                $sql = "UPDATE referensi_biaya SET kode = :new_code, jenis = :jenis,  
                    modified_by = :modified_by, modified_at = NOW() WHERE kode = :old_code";
                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(":new_code", $params->new_kode, PDO::PARAM_STR);
                $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
                $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(":old_code", $kode, PDO::PARAM_STR);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                {
                    $g->response["result"] = [
                        "kode" => $kode,
                        "jenis" => $jenis,
                    ];
                    setResponseStatus(true, "Update referensi biaya berhasil!");
                }
                else
                    setResponseStatus(false, "Tidak ada perubahan data!");
            }
            else
                setResponseStatus(false, "Jenis referensi biaya sudah ada sebelumnya!");
        }
        else
            setResponseStatus(false, "Kode referensi biaya sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi biaya tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/biaya/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $kode = strtoupper(trim($kode));
    $sql = "DELETE FROM referensi_biaya WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi biaya " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi biaya " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/biaya/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM referensi_biaya 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT kode, jenis, created_at FROM referensi_biaya 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi biaya ditemukan!");
});

// REFERENSI PELANGGAN: UI 100%
Flight::route("POST|PUT /referensi/pelanggan/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "nama, alamat, token");
    // check existing alamat
    $alamat = strtoupper(trim($params->alamat));
    $sql = "SELECT alamat FROM referensi_pelanggan WHERE UPPER(alamat) = :alamat";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":alamat", $alamat, PDO::PARAM_STR);
    $pdo->execute();

    $isAlamatExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isAlamatExist)
    {
        $params->nama = strtoupper(trim($params->nama));
        $data = [
            "nama" => [$params->nama, "string"],
            "alamat" => [$alamat, "string"],
            "created_by" => [$g->logged->username, "string"]
        ];
        $pdo->insert("referensi_pelanggan", $data);

        $g->response["result"] = [
            "id" => $pdo->con->lastInsertId(),
            "nama" => $params->nama,
            "alamat" => $alamat,
        ];
        setResponseStatus(true, "Referensi pelanggan berhasil ditambahkan!");
    }
    else
        setResponseStatus(false, "Alamat referensi pelanggan sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/pelanggan/edit/@id", function($id) use ($g, $pdo, $params) {

    requiredFields($params, "nama, alamat, token");
    // check if kode exist
    $sql = "SELECT id FROM referensi_pelanggan WHERE id = :id";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":id", $id, PDO::PARAM_STR);
    $pdo->execute();

    $isPelangganExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isPelangganExist)
    {
        // check if new alamat available
        $alamat = trim(strtoupper($params->alamat));
        $sql = "SELECT id FROM referensi_pelanggan WHERE UPPER(alamat) = :alamat AND id != :id";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":alamat", $alamat, PDO::PARAM_STR);
        $pdo->sth->bindParam(":id", $id, PDO::PARAM_STR);
        $pdo->execute();

        $isNewAlamatExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewAlamatExist) 
        {
            $nama = trim(strtoupper($params->nama));
            $sql = "UPDATE referensi_pelanggan SET nama = :nama, alamat = :alamat,  
                modified_by = :modified_by, modified_at = NOW() WHERE id = :id";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":nama", $nama, PDO::PARAM_STR);
            $pdo->sth->bindParam(":alamat", $alamat, PDO::PARAM_STR);
            $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
            $pdo->sth->bindParam(":id", $id, PDO::PARAM_STR);
            $pdo->execute();

            $affectedRow = $pdo->sth->rowCount();
            if ($affectedRow > 0)
            {
                $g->response["result"] = [
                    "id" => $id,
                    "nama" => $nama,
                    "alamat" => $alamat,
                ];
                setResponseStatus(true, "Update referensi pelanggan berhasil!");
            }
            else
                setResponseStatus(false, "Tidak ada perubahan data!");
        }
        else
            setResponseStatus(false, "Alamat referensi pelanggan sudah ada sebelumnya!");

    }
    else
        setResponseStatus(false, "Referensi pelanggan tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/pelanggan/delete/@id", function($id) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $sql = "DELETE FROM referensi_pelanggan WHERE id = :id";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":id", $id, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi pelanggan " . $id . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi pelanggan " . $id . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/pelanggan/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(id) as total_data FROM referensi_pelanggan 
        WHERE (nama LIKE CONCAT('%', :key, '%') OR alamat LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT id, nama, alamat, created_at FROM referensi_pelanggan
        WHERE (nama LIKE CONCAT('%', :key, '%') OR alamat LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi pelanggan ditemukan!");
});

// REFERENSI KHS: UI
Flight::route("POST|PUT /referensi/khs/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "kode, jenis, harga, token");
    // check existing kode
    $params->kode = trim(strtoupper($params->kode));
    if (!isRowExist("referensi_khs", "kode", $params->kode))
    {
        $params->jenis = trim(strtoupper($params->jenis));
        if (!isRowExist("referensi_khs", "jenis", $params->jenis))
        {
            $data = [
                "kode" => [$params->kode, "string"],
                "jenis" => [$params->jenis, "string"],
                "harga" => [$params->harga, "int"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_khs", $data);

            $g->response["result"] = [
                "kode" => $params->kode,
                "jenis" => $params->jenis,
                "harga" => $params->harga
            ];
            setResponseStatus(true, "Referensi khs berhasil ditambahkan!");
        }
        else
            setResponseStatus(false, "Jenis referensi khs sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Kode referensi khs sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/khs/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_kode, jenis, harga, token");
    // check if kode exist
    $params->new_kode = trim(strtoupper($params->new_kode));
    if (isRowExist("referensi_khs", "kode", $params->new_kode))
    {
        // check if new jenis available
        $jenis = trim(strtoupper($params->jenis));
        $sql = "SELECT kode FROM referensi_khs WHERE UPPER(jenis) = :jenis AND kode != :kode";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
        $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewJenisExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewJenisExist) 
        {
            $sql = "UPDATE referensi_khs SET kode = :new_kode, jenis = :jenis, harga = :harga,   
                modified_by = :modified_by, modified_at = NOW() WHERE kode = :kode";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":new_kode", $params->new_kode, PDO::PARAM_STR);
            $pdo->sth->bindParam(":jenis", $jenis, PDO::PARAM_STR);
            $pdo->sth->bindParam(":harga", $params->harga, PDO::PARAM_STR);
            $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
            $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $affectedRow = $pdo->sth->rowCount();
            if ($affectedRow > 0)
            {
                $g->response["result"] = [
                    "kode" => $kode,
                    "jenis" => $jenis,
                    "harga" => $params->harga
                ];
                setResponseStatus(true, "Update referensi khs berhasil!");
            }
            else
                setResponseStatus(false, "Tidak ada perubahan data!");
        }
        else
            setResponseStatus(false, "Referensi khs sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi khs tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/khs/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $sql = "DELETE FROM referensi_khs WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi khs " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi khs " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/khs/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM referensi_khs 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT kode, jenis, harga, created_at FROM referensi_khs
        WHERE (kode LIKE CONCAT('%', :key, '%') OR jenis LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi khs ditemukan!");
});

// REFERENSI POP: UI 100%
Flight::route("POST|PUT /referensi/pop/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "kode, pop, token");
    // check existing kode
    $kode = strtoupper(trim($params->kode));
    $sql = "SELECT kode FROM referensi_pop WHERE UPPER(kode) = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isKodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isKodeExist)
    {
        // check existing kode
        $pop = strtoupper(trim($params->pop));
        $sql = "SELECT kode FROM referensi_pop WHERE UPPER(pop) = :pop";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":pop", $pop, PDO::PARAM_STR);
        $pdo->execute();

        $isPopExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isPopExist)
        {
            $data = [
                "kode" => [$kode, "string"],
                "pop" => [$pop, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_pop", $data);

            $g->response["result"] = [
                "kode" => $kode,
                "pop" => $pop,
            ];
            setResponseStatus(true, "Referensi POP berhasil ditambahkan!");
        }
        else
            setResponseStatus(false, "Referensi POP sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Kode referensi POP sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/pop/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_kode, pop, token");
    // check if kode exist
    $sql = "SELECT kode FROM referensi_pop WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isKodeExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isKodeExist)
    {
        // check if new pop available
        $pop = trim(strtoupper($params->pop));
        $sql = "SELECT kode FROM referensi_pop WHERE UPPER(pop) = :pop AND kode != :kode";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":pop", $pop, PDO::PARAM_STR);
        $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewPopExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewPopExist) 
        {
            $pop = trim(strtoupper($params->pop));
            $sql = "UPDATE referensi_pop SET kode = :new_kode, pop = :pop,  
                modified_by = :modified_by, modified_at = NOW() WHERE kode = :kode";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":new_kode", $params->new_kode, PDO::PARAM_STR);
            $pdo->sth->bindParam(":pop", $pop, PDO::PARAM_STR);
            $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
            $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_STR);
            $pdo->execute();

            $affectedRow = $pdo->sth->rowCount();
            if ($affectedRow > 0)
            {
                $g->response["result"] = [
                    "kode" => $kode,
                    "pop" => $pop
                ];
                setResponseStatus(true, "Update referensi POP berhasil!");
            }
            else
                setResponseStatus(false, "Tidak ada perubahan data!");
        }
        else
            setResponseStatus(false, "Referensi POP sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi POP tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/pop/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $sql = "DELETE FROM referensi_pop WHERE kode = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi POP " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi pelanggan " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/pop/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(kode) as total_data FROM referensi_pop 
        WHERE (kode LIKE CONCAT('%', :key, '%') OR pop LIKE CONCAT('%', :key, '%'))";
    
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

    $sql = "SELECT kode, pop, created_at FROM referensi_pop
        WHERE (kode LIKE CONCAT('%', :key, '%') OR pop LIKE CONCAT('%', :key, '%'))   
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi POP ditemukan!");
});

// REFERENSI NO. PLAT: UI 100%
Flight::route("POST|PUT /referensi/plat/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "no_plat, token");
    // check existing no_plat
    $no_plat = strtoupper(trim($params->no_plat));
    $sql = "SELECT no_plat FROM referensi_plat WHERE UPPER(no_plat) = :no_plat";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_plat", $params->no_plat, PDO::PARAM_STR);
    $pdo->execute();

    $isPopExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isPopExist)
    {
        $data = [
            "no_plat" => [$no_plat, "string"],
            "created_by" => [$g->logged->username, "string"]
        ];
        $pdo->insert("referensi_plat", $data);

        $g->response["result"] = [
            "no_plat" => $no_plat
        ];
        setResponseStatus(true, "Referensi plat berhasil ditambahkan!");
    }
    else
        setResponseStatus(false, "Referensi plat sudah ada sebelumnya!");
    
});

Flight::route("POST|PUT /referensi/plat/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_plat, token");
    
    // check if new pop available
    $new_plat = trim(strtoupper($params->new_plat));
    $sql = "SELECT no_plat FROM referensi_plat WHERE UPPER(no_plat) = :no_plat_new AND no_plat != :no_plat_old";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_plat_new", $new_plat, PDO::PARAM_STR);
    $pdo->sth->bindParam(":no_plat_old", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isNewPopExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isNewPopExist) 
    {
        $sql = "UPDATE referensi_plat SET no_plat = :new_plat, 
            modified_by = :modified_by, modified_at = NOW() WHERE no_plat = :old_plat";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_plat", $new_plat, PDO::PARAM_STR);
        $pdo->sth->bindParam(":old_plat", $kode, PDO::PARAM_STR);
        $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
        $pdo->execute();

        $affectedRow = $pdo->sth->rowCount();
        if ($affectedRow > 0)
        {
            $g->response["result"] = [
                "no_plat" => $new_plat
            ];
            setResponseStatus(true, "Update referensi plat berhasil!");
        }
        else
            setResponseStatus(false, "Tidak ada perubahan data!");
    }
    else
        setResponseStatus(false, "Referensi plat sudah ada sebelumnya!");
});

Flight::route("POST|PUT /referensi/plat/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $sql = "DELETE FROM referensi_plat WHERE no_plat = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi plat " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi plat " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/plat/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(no_plat) as total_data FROM referensi_plat 
        WHERE no_plat LIKE CONCAT('%', :key, '%')";
    
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

    $sql = "SELECT no_plat, created_at FROM referensi_plat
        WHERE no_plat LIKE CONCAT('%', :key, '%') 
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi plat ditemukan!");
});

// REFERENSI E-TOLL: UI 100%
Flight::route("POST|PUT /referensi/etoll/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "no_kartu, token");
    // check existing no_kartu
    $no_kartu = strtoupper(trim($params->no_kartu));
    $sql = "SELECT no_kartu FROM referensi_etoll WHERE UPPER(no_kartu) = :no_kartu";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_kartu", $params->no_kartu, PDO::PARAM_STR);
    $pdo->execute();

    $isPopExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isPopExist)
    {
        $data = [
            "no_kartu" => [$no_kartu, "string"],
            "created_by" => [$g->logged->username, "string"]
        ];
        $pdo->insert("referensi_etoll", $data);

        $g->response["result"] = [
            "no_kartu" => $no_kartu
        ];
        setResponseStatus(true, "Referensi etoll berhasil ditambahkan!");
    }
    else
        setResponseStatus(false, "Referensi etoll sudah ada sebelumnya!");
    
});

Flight::route("POST|PUT /referensi/etoll/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_no_kartu, token");
    $new_no_kartu = trim(strtoupper($params->new_no_kartu));
    $sql = "SELECT no_kartu FROM referensi_etoll WHERE UPPER(no_kartu) = :no_kartu";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_kartu", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isOldExist = $pdo->sth->fetch(PDO::FETCH_OBJ);

    if ($isOldExist)
    {
        // check if new available
        $sql = "SELECT no_kartu FROM referensi_etoll WHERE UPPER(no_kartu) = :new_no_kartu AND no_kartu != :no_kartu";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_no_kartu", $new_no_kartu, PDO::PARAM_STR);
        $pdo->sth->bindParam(":no_kartu", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewExist) 
        {
            $sql = "UPDATE referensi_etoll SET no_kartu = :new_no_kartu, 
                modified_by = :modified_by, modified_at = NOW() WHERE no_kartu = :old_kartu";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":new_no_kartu", $new_no_kartu, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_kartu", $kode, PDO::PARAM_STR);
            $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
            $pdo->execute();

            $affectedRow = $pdo->sth->rowCount();
            if ($affectedRow > 0)
            {
                $g->response["result"] = [
                    "no_kartu" => $new_no_kartu
                ];
                setResponseStatus(true, "Update referensi etoll berhasil!");
            }
            else
                setResponseStatus(false, "Tidak ada perubahan data!");
        }
        else
            setResponseStatus(false, "Referensi etoll sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi etoll tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/etoll/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $sql = "DELETE FROM referensi_etoll WHERE no_kartu = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi etoll " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi etoll " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/etoll/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(no_kartu) as total_data FROM referensi_etoll 
        WHERE no_kartu LIKE CONCAT('%', :key, '%')";
    
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

    $sql = "SELECT no_kartu, created_at FROM referensi_etoll
        WHERE no_kartu LIKE CONCAT('%', :key, '%') 
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi etoll ditemukan!");
});

// REFERENSI HP
Flight::route("POST|PUT /referensi/hp/add", function() use ($g, $pdo, $params) {

    requiredFields($params, "no_kartu, token");
    // check existing no_kartu
    $no_kartu = strtoupper(trim($params->no_kartu));
    $sql = "SELECT no_kartu FROM referensi_hp WHERE UPPER(no_kartu) = :no_kartu";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_kartu", $params->no_kartu, PDO::PARAM_STR);
    $pdo->execute();

    $isPopExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isPopExist)
    {
        $data = [
            "no_kartu" => [$no_kartu, "string"],
            "created_by" => [$g->logged->username, "string"]
        ];
        $pdo->insert("referensi_hp", $data);

        $g->response["result"] = [
            "no_kartu" => $no_kartu
        ];
        setResponseStatus(true, "Referensi hp berhasil ditambahkan!");
    }
    else
        setResponseStatus(false, "Referensi hp sudah ada sebelumnya!");
    
});

Flight::route("POST|PUT /referensi/hp/edit/@kode", function($kode) use ($g, $pdo, $params) {

    requiredFields($params, "new_no_kartu, token");
    $new_no_kartu = trim(strtoupper($params->new_no_kartu));
    $sql = "SELECT no_kartu FROM referensi_hp WHERE UPPER(no_kartu) = :no_kartu";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":no_kartu", $kode, PDO::PARAM_STR);
    $pdo->execute();

    $isOldExist = $pdo->sth->fetch(PDO::FETCH_OBJ);

    if ($isOldExist)
    {
        // check if new available
        $sql = "SELECT no_kartu FROM referensi_hp WHERE UPPER(no_kartu) = :new_no_kartu AND no_kartu != :no_kartu";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":new_no_kartu", $new_no_kartu, PDO::PARAM_STR);
        $pdo->sth->bindParam(":no_kartu", $kode, PDO::PARAM_STR);
        $pdo->execute();

        $isNewExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isNewExist) 
        {
            $sql = "UPDATE referensi_hp SET no_kartu = :new_no_kartu, 
                modified_by = :modified_by, modified_at = NOW() WHERE no_kartu = :old_kartu";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam(":new_no_kartu", $new_no_kartu, PDO::PARAM_STR);
            $pdo->sth->bindParam(":old_kartu", $kode, PDO::PARAM_STR);
            $pdo->sth->bindParam(":modified_by", $g->logged->username, PDO::PARAM_STR);
            $pdo->execute();

            $affectedRow = $pdo->sth->rowCount();
            if ($affectedRow > 0)
            {
                $g->response["result"] = [
                    "no_kartu" => $new_no_kartu
                ];
                setResponseStatus(true, "Update referensi hp berhasil!");
            }
            else
                setResponseStatus(false, "Tidak ada perubahan data!");
        }
        else
            setResponseStatus(false, "Referensi hp sudah ada sebelumnya!");
    }
    else
        setResponseStatus(false, "Referensi hp tidak ditemukan!");
});

Flight::route("POST|PUT /referensi/hp/delete/@kode", function($kode) use ($g, $pdo, $params) {
    
    requiredFields($params, "token");
    $sql = "DELETE FROM referensi_hp WHERE no_kartu = :kode";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":kode", $kode, PDO::PARAM_INT);
    $pdo->execute();

    if ($pdo->sth->rowCount() > 0)
        setResponseStatus(true, "Referensi hp " . $kode . " berhasil di hapus!"); 
    else
        setResponseStatus(false, "Delete gagal atau referensi hp " . $kode . " tidak ditemukan!"); 
});

Flight::route("POST|PUT /referensi/hp/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {
   
    requiredFields($params, "order, sort, token");
    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    $sql = "SELECT COUNT(no_kartu) as total_data FROM referensi_hp 
        WHERE no_kartu LIKE CONCAT('%', :key, '%')";
    
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

    $sql = "SELECT no_kartu, created_at FROM referensi_hp
        WHERE no_kartu LIKE CONCAT('%', :key, '%') 
        ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Referensi hp ditemukan!");
});

// PETTY CASH