<?php 

Flight::route("POST|PUT /kansi_wip/aktivasi", function() use ($g, $pdo, $params) {

    $params->posisi_tgl = $params->posisi_tgl . " 23:59:59";

    // ambil semua aktivasi SO
    $sql = "SELECT no_so, pkb_no_bukti, pkt_no_bukti FROM aktivasi_so ORDER BY created_at ASC";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->execute();

    $g->response["result"]["total"] = 0;
    while ($row = $pdo->sth->fetch(PDO::FETCH_OBJ, PDO::FETCH_ORI_NEXT))
    {
        $row->wip_so_biaya_lain = "";
        $row->wip_so_jasa = "";
        $row->wip_so_material = "";
        $row->wip_pkt_nomor = "";
        $row->wip_pkt_jasa = "";
        $row->wip_pkt_material = "";
        $row->wip_gr = "";
        $row->wip_invoice_nomor = "";
        $row->wip_invoice_dpp = "";
        $row->wip_hpp_jasa = "";
        $row->wip_hpp_biaya_lain = "";
        $row->wip_hpp_material = "";

        $pdoChild = new MyPdo();

        // hitung total so biaya_lain
        $sql = "SELECT SUM(sobld.nilai) AS wip_so_biaya_lain FROM aktivasi_so_biaya_lain AS sobl
            JOIN aktivasi_so_biaya_lain_detail AS sobld ON sobl.no_bukti = sobld.no_bukti
            WHERE sobl.no_so = '".$row->no_so."' AND sobl.created_at < '".$params->posisi_tgl."'";
        
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();
        $rowResult = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

        if ((int) $row->wip_so_biaya_lain > 0)
            $row->wip_so_biaya_lain = $rowResult->wip_so_biaya_lain;

        // hitung total so jasa
        $sql = "SELECT SUM(sojd.nilai) AS wip_so_jasa FROM aktivasi_so_jasa AS soj
            JOIN aktivasi_so_jasa_detail AS sojd ON soj.no_bukti = sojd.no_bukti
            WHERE soj.no_so = '".$row->no_so."' AND soj.created_at < '".$params->posisi_tgl."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();
        $rowResult = $pdoChild->sth->fetch(PDO::FETCH_OBJ);

        if ((int) $row->wip_so_jasa > 0)
            $row->wip_so_jasa = $rowResult->wip_so_jasa;

        // hitung total so material, get each material pkb
        $sql = "SELECT kode_material FROM aktivasi_so_pkb_material WHERE no_bukti = '".$row->pkb_no_bukti."'";
        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();
        while ($rowMaterial = $pdoChild->sth->fetch(PDO::FETCH_OBJ, PDO::FETCH_ORI_NEXT))
        {
            // ambil masing masing nilai material dari kertas kerja
            $pdoGC = new MyPdo();
            $sql = "SELECT SUM(pengeluaran_nilai) AS total_biaya_aktivasi
                        FROM material_kertas_kerja 
                        WHERE jenis_usaha = 'AKTIVASI' AND tgl < '".$params->posisi_tgl."' 
                        AND kode_material = '".$rowMaterial->kode_material."' AND no_referensi = '".$row->no_so."'";

            $pdoGC->sth = $pdoChild->con->prepare($sql);
            $pdoGC->execute();

            $aktivasiTmp = $pdoGC->sth->fetch(PDO::FETCH_OBJ);
            $row->wip_so_material = (int) $row->wip_so_material + (int) $aktivasiTmp->total_biaya_aktivasi;
        }

        // wip material harus dikurangi yang di retur
        $sql = "SELECT SUM(perolehan_nilai) AS total_biaya_aktivasi_retur
                    FROM material_kertas_kerja 
                    WHERE jenis_usaha = 'AKTIVASI-RETUR' AND tgl < '".$params->posisi_tgl."' AND no_referensi = '".$row->no_so."'";

        $pdoChild->sth = $pdoChild->con->prepare($sql);
        $pdoChild->execute();

        $kertasKerjaRetur = $pdoChild->sth->fetch(PDO::FETCH_OBJ);
        $row->wip_so_material -= (int) $kertasKerjaRetur->total_biaya_aktivasi_retur;

        // hitung wip aktivasi pkt

        // hitung wip aktivasi invoice

        // hitung wip aktivasi hpp

        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." WIP Aktivasi(s) retrieved!");
});
