<?php

Flight::route("POST|PUT /kansi_rekap/biaya_material", function() use ($g, $pdo, $params) {

    // query yang ribet ky gini harusnya bisa pake store procedure -> ntar lah ga keburu

    $params->posisi_tgl .= " 23:59:59";

    // get distinct material
    $sql = "SELECT kode, jenis FROM referensi_material ORDER BY kode ASC";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->execute();

    $g->response["result"]["total"] = 0;
    while ($row = $pdo->sth->fetch(PDO::FETCH_OBJ, PDO::FETCH_ORI_NEXT))
    {
        $row->biaya_serpo = "";
        $row->qty_serpo = "";
        $row->biaya_aktivasi = "";
        $row->qty_aktivasi = "";
        $row->biaya_imp = "";
        $row->qty_imp = "";
        $row->biaya_sale = "";
        $row->qty_sale = "";

        $pdoChild = new MyPdo();
        // get biaya dan kuantitas aktivasi
        $sql = "SELECT SUM(pengeluaran_nilai) AS total_biaya_aktivasi, 
                    SUM(pengeluaran_qty) AS total_qty_aktivasi 
                    FROM material_kertas_kerja 
                    WHERE jenis_usaha = 'AKTIVASI' AND tgl < '".$params->posisi_tgl."' 
                    AND kode_material = '".$row->kode."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();

        $aktivasiTmp = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        if ((int) $aktivasiTmp->total_biaya_aktivasi > 0)
            $row->biaya_aktivasi = (int) $aktivasiTmp->total_biaya_aktivasi;
        
        if ((int) $aktivasiTmp->total_qty_aktivasi > 0)
            $row->qty_aktivasi = (int) $aktivasiTmp->total_qty_aktivasi;

        // total biaya aktivasi dan qty aktivasi harus dikurangi retur
        $sql = "SELECT SUM(perolehan_nilai) AS total_biaya_aktivasi_retur, 
                    SUM(perolehan_qty) AS total_qty_aktivasi_retur 
                    FROM material_kertas_kerja 
                    WHERE jenis_usaha = 'AKTIVASI-RETUR' AND tgl < '".$params->posisi_tgl."' 
                    AND kode_material = '".$row->kode."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();

        $akRetur = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        if ((int) $akRetur->total_biaya_aktivasi_retur > 0)
            $row->biaya_aktivasi -= (int) $akRetur->total_biaya_aktivasi_retur;
        
        if ((int) $akRetur->total_qty_aktivasi_retur > 0)
            $row->qty_aktivasi -= (int) $akRetur->total_qty_aktivasi_retur;

        // total biaya aktivasi dan qty aktivasi harus dikurangi juga oleh loss / kerugian
        $sql = "SELECT SUM(perolehan_nilai) AS total_biaya_aktivasi_loss, 
                    SUM(perolehan_qty) AS total_qty_aktivasi_loss 
                    FROM material_kertas_kerja 
                    WHERE jenis_usaha = 'AKTIVASI-KERUGIAN' AND tgl < '".$params->posisi_tgl."' 
                    AND kode_material = '".$row->kode."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();

        $akLoss = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        if ((int) $akLoss->total_biaya_aktivasi_loss > 0)
            $row->biaya_aktivasi -= (int) $akLoss->total_biaya_aktivasi_loss;
        
        if ((int) $akLoss->total_qty_aktivasi_loss > 0)
            $row->qty_aktivasi -= (int) $akLoss->total_qty_aktivasi_loss;

        // get biaya dan kuantitas serpo
        $sql = "SELECT SUM(pengeluaran_nilai) AS total_biaya_serpo, 
                    SUM(pengeluaran_qty) AS total_qty_serpo 
                    FROM material_kertas_kerja 
                    WHERE jenis_usaha = 'SERPO' AND tgl < '".$params->posisi_tgl."' 
                    AND kode_material = '".$row->kode."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();

        $serpoTmp = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        if ((int) $serpoTmp->total_biaya_serpo > 0)
            $row->biaya_serpo = (int) $serpoTmp->total_biaya_serpo;
        
        if ((int) $serpoTmp->total_qty_serpo > 0)
            $row->qty_serpo = (int) $serpoTmp->total_qty_serpo;

        // get biaya dan kuantitas imp
        $sql = "SELECT SUM(pengeluaran_nilai) AS total_biaya_imp, 
                    SUM(pengeluaran_qty) AS total_qty_imp 
                    FROM material_kertas_kerja 
                    WHERE jenis_usaha = 'IMP' AND tgl < '".$params->posisi_tgl."' 
                    AND kode_material = '".$row->kode."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();

        $impTmp = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        if ((int) $impTmp->total_biaya_imp > 0)
            $row->biaya_imp = (int) $impTmp->total_biaya_imp;
        
        if ((int) $impTmp->total_qty_imp > 0)
            $row->qty_imp = (int) $impTmp->total_qty_imp;

        // get biaya dan kuantitas imp
        $sql = "SELECT SUM(pengeluaran_nilai) AS total_biaya_sale, 
                    SUM(pengeluaran_qty) AS total_qty_sale 
                    FROM material_kertas_kerja 
                    WHERE jenis_usaha = 'IMP' AND tgl < '".$params->posisi_tgl."' 
                    AND kode_material = '".$row->kode."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();

        $saleTmp = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        if ((int) $saleTmp->total_biaya_sale > 0)
            $row->biaya_sale = (int) $saleTmp->total_biaya_sale;
        
        if ((int) $saleTmp->total_qty_sale > 0)
            $row->qty_sale = (int) $saleTmp->total_qty_sale;

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." Rekap Biaya Material(s) retrieved!");
});