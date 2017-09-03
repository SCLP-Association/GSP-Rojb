<?php

// START AKTIVASI RUTIN
Flight::route("/unit/aktivasi/rutin", function() {
    
    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "no_bukti",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];

    $header = [
        "title" => "Unit Aktivasi Rutin " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 321, "fourth" => 0]
    ];

    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];

    $response = getApiResponse(WEB_API . "aktivasi/rutin/search/1/150/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = ["data" => $response->result->data, "key" => $key, "range_date" => $range_date, "from_date" => $from_date, "to_date" => $to_date];
    renderView($header, $dataOutput, "unit/aktivasi/rutin.php");
});

Flight::route("/unit/aktivasi/input", function() {
    $header = [
        "title" => "Unit Aktivasi Rutin Input " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 321, "fourth" => 0]
    ];

    // get autonumber aktivasi rutin
    $data = ["type" => "AKTIVASI-RUTIN", "token" => $_SESSION["token"]];
    $responseNo = getApiResponse(WEB_API . "util/autonumber/create", $data);
    $noBuktiAktivasiEx = explode("/", $responseNo->result->auto_number);
    $noKuitansiSuffix = $noBuktiAktivasiEx[0] . "/" . $noBuktiAktivasiEx[1];

    // get no kuitansi
    $data = ["type" => "KUITANSI-AKTIVASI-RUTIN", "no_bukti" => $noKuitansiSuffix, "token" => $_SESSION["token"]];
    $responseNoKuitansi = getApiResponse(WEB_API . "util/autonumber/create", $data);

    // get jenis biaya
    $data = ["order" => "kode", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseBiaya = getApiResponse(WEB_API . "referensi/biaya/search/1/150/_", $data);

    $dataOutput = [
        "biaya" => $responseBiaya->result->data, 
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "no_bukti" => $responseNo->result->auto_number,
        "no_kuitansi" => $responseNoKuitansi->result->auto_number,
        "admin_kuitansi" => $_SESSION["name"]
    ];
    renderView($header, $dataOutput, "unit/aktivasi/input.php");
});

Flight::route("/unit/aktivasi/input_load_template/@kode", function($kode) {
    // load no plat
    $data = ["order" => "no_plat", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseNoplat = getApiResponse(WEB_API . "referensi/plat/search/1/100/_", $data);
    $dataOutput["no_plat"] = $responseNoplat->result->data;

    // load no kartu etoll
    $data = ["order" => "no_kartu", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseEtoll = getApiResponse(WEB_API . "referensi/etoll/search/1/100/_", $data);
    $dataOutput["etoll"] = $responseEtoll->result->data;

    // load regional
    $data = ["order" => "wilayah", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseRegional = getApiResponse(WEB_API . "referensi/regional/search/1/100/_", $data);
    $dataOutput["regional"] = $responseRegional->result->data;

    // load no kartu hp
    $data = ["order" => "no_kartu", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseHp = getApiResponse(WEB_API . "referensi/hp/search/1/100/_", $data);
    $dataOutput["hp"] = $responseHp->result->data;

    Flight::render("unit/aktivasi/input_" . $kode . ".php", $dataOutput);
});

Flight::route("/unit/aktivasi/do_input", function() {
    $input = $_POST;
    
    $count = 0;
    $detail = [];

    switch ($input["kode_biaya"])
    {
        case "B0100":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", Q: " . $input["qty"][$idx]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$idx]);
                $count++;
            }
            break;
        
        case "B0200":
            foreach ($input["kendaraan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO. PLAT: " . $input["no_plat"][$count] . ", LTR: " . $input["ltr"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0300":
            foreach ($input["ormas"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", NO. AR: " . $input["no_ar"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0400":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
        case "B0500":
            foreach ($input["pulsa"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO. KARTU: " . $input["no_hp"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0600":
            foreach ($input["listrik"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0700":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0800":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0900":
            if ($input["kendaraan"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("KENDARAAN, NO. PLAT: " . $input["no_plat"] . ", KET: " . $input["keterangan_1"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_1"]) + (int) str_replace(".", "", $input["material_rp_1"]);
                $count++;
            }

            if ($input["bangunan"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("BANGUNAN, REG: " . $input["regional_1"] . ", KET: " . $input["keterangan_2"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_2"]) + (int) str_replace(".", "", $input["material_rp_2"]);
                $count++;
            }

            if ($input["lainnya"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("LAINNYA, REG: " . $input["regional_2"] . ", KET: " . $input["keterangan_3"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_3"]) + (int) str_replace(".", "", $input["material_rp_3"]);
                $count++;
            }
            break;

        case "B1000":                
            foreach ($input["no_ar"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NO. AR: " . $row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1100":
            foreach ($input["parkir"] as $idx => $row)
            {
                switch ($row[0])
                {
                    case "PARKIR":
                        $detail[$count]["deskripsi"] = strtoupper($row[0]);
                        break;

                    case "TOL":
                        $detail[$count]["deskripsi"] = strtoupper($row[0] . ", LOK: " . $input["lokasi_tol"][$count]);
                        break;

                    case "E-TOL":
                        $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO: " . $input["no_etoll"][$count]);
                        break;
                }

                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
    
        case "B1200":
            foreach ($input["nama"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NAMA: " . $row . ", INST: " . $input["inst"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1300":
            foreach ($input["sewa"] as $idx => $row)
            {
                if ($row != "0")
                {
                    $desk = strtoupper("DET: " . $input["detail"][$count] . ", PER: " . $input["periode"][$count] . ", PEM: " . $input["pemilik"][$count]);
                    $detail[$count]["deskripsi"] = $desk;
                    $detail[$count]["nilai"] = str_replace(".", "", $input["rp"][$count]);
                }
                $count++;
            }
            break;

        case "B1400":
            $desk = strtoupper($input["jenis_jasa"]. ", " . $input["jenis_biaya"] . ", " . $input["penyedia_jasa"] . ", " . $input["no_ktp"]);
            $detail[$count]["deskripsi"] = $desk;
            $detail[$count]["nilai"] = str_replace(".", "", $input["biaya_jasa"]);
            break;

        case "B1500":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1600":
            foreach ($input["nama"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NAMA: " . $row . ", MOJU: " . $input["moju"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1700":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", SN: " . $input["sn"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
    }

    $data = [
        "no_bukti" => $input["no_bukti"],
        "tgl" => $input["tgl"],
        "kode_biaya" => $input["kode_biaya"],
        "jenis_biaya" => $input["jenis_biaya"],
        "no_kuitansi" => ($input["no_kuitansi"] == "0") ? "" : $input["no_kuitansi"],
        "tgl_kuitansi" => ($input["tgl_kuitansi"] == "0") ? "" : $input["tgl_kuitansi"],
        "admin_kuitansi" => ($input["admin_kuitansi"] == "0") ? "" : $input["admin_kuitansi"],
        "penerima_kuitansi" => ($input["penerima_kuitansi"] == "0") ? "" : $input["penerima_kuitansi"],
        "id_kuitansi" => ($input["id_kuitansi"] == "0") ? "" : $input["id_kuitansi"],
        "detail" => $detail,
        "token" => $_SESSION["token"]
    ];

    $response = getApiResponse(WEB_API . "aktivasi/rutin/create", $data);
    echo json_encode($response);
});

Flight::route("/unit/aktivasi/load_no_bukti_baru", function() {
    // get autonumber aktivasi rutin
    $data = ["type" => "AKTIVASI-RUTIN", "token" => $_SESSION["token"]];
    $responseNo = getApiResponse(WEB_API . "util/autonumber/create", $data);
    $noBuktiAktivasiEx = explode("/", $responseNo->result->auto_number);
    $noKuitansiSuffix = $noBuktiAktivasiEx[0] . "/" . $noBuktiAktivasiEx[1];

    // get no kuitansi
    $data = ["type" => "KUITANSI-AKTIVASI-RUTIN", "no_bukti" => $noKuitansiSuffix, "token" => $_SESSION["token"]];
    $responseNoKuitansi = getApiResponse(WEB_API . "util/autonumber/create", $data);

    $data = [
        "no_bukti" => $responseNo->result->auto_number,
        "no_kuitansi" => $responseNoKuitansi->result->auto_number,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"),
    ];

    echo json_encode($data);
});

Flight::route("/unit/aktivasi/kuitansi/@no_kuitansi", function($no_kuitansi) {
    // get detail kuitansi
    $noKuitansi = strReplaceFirst("-", "/", $no_kuitansi);
    $data = [
        "key" => $noKuitansi,
        "order" => "no_bukti",
        "sort" => "asc",
        "search_by" => "no_kuitansi",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $responseKuitansi = getApiResponse(WEB_API . "aktivasi/rutin/search_by/1/1", $data);
    $kw = $responseKuitansi->result->data[0];

    $total = 0;
    $untukPembayaran = "";
    foreach ($kw->detail as $id => $row)
    {
        $total += (int) $row->nilai;
        $untukPembayaran .= $row->deskripsi." Rp. ".toRupiah($row->nilai)." ";
    }

    $pdf = new FPDF();
    $pdf->AddPage("L", "A5");
    $pdf->setAuthor("Aditia Rahman");
    $pdf->SetFont("Arial", "B", 12);
    $pdf->Image("themes/img/gsp-logo-long.jpg", 10, 10, 100);
    $pdf->Cell(120);

    $pdf->Cell(0, 5, "KUITANSI GSP", 0, 1);
    $pdf->Cell(120);
    $pdf->SetFont("Arial", "",10);
    $pdf->Cell(0, 10, "No. ".$noKuitansi, 0, 1);

    $pdf->Cell(10, 3, "", 0, 1);
    $pdf->Cell(32, 6, "Sudah terima dari", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": PT GERBANG SINERGI PRIMA", 0, 1);

    $pdf->Cell(32, 6, "Banyaknya uang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": Rp. ".toRupiah($total), 0, 1);

    $pdf->Cell(32, 6, "Terbilang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ":".strtoupper(rpTerbilang($total))." RUPIAH", 0, 1);

    // $pdf->Cell(32, 7, "Untuk pembayaran", 0, 0);
    // $pdf->Cell(5);
    // $pdf->Cell(85, 7, ": ".$untukPembayaran, 0, 1);

    foreach ($kw->detail as $id => $row)
    {
        if ($id == 0)
            $pdf->Cell(32, 6, "Untuk pembayaran", 0, 0);
        else
            $pdf->Cell(32, 6, "", 0, 0);

        $pdf->Cell(5);
        $pdf->Cell(85, 6, ": ".$row->deskripsi." Rp. ".toRupiah($row->nilai), 0, 1);
    }

    // separator
    $pdf->Cell(32, 15, "", 0, 1);

    $pdf->Cell(32, 6, "Tanda Tangan", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 6, "__________________________", 0, 0);

    $pdf->SetFont("Arial", "", 9);
    $pdf->Cell(1);
    $pdf->Cell(82, 5, "Saya menjamin kebenaran dan bertanggung jawab", "TLR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "sepenuhnya atas seluruh informasi yang terdapat dalam", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "kuitansi ini.", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 3, "", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "Bendahara	: WAHYU (_____________________)", "LRB", 1);

    $pdf->Cell(32, 5, "Nama Penerima", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5,": ".$kw->penerima_kuitansi, 0, 1);

    $pdf->Cell(32, 5, "ID", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5, ": ".$kw->id_kuitansi, 0, 1);

    $pdf->Cell(10, 1, "", 0, 1);
    $pdf->Cell(10, 1, '________________________________________________________________________________________________________', 0, 1);

    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(125);
    $pdf->Cell(82, 6, "NO. BUKTI: ".$kw->no_bukti."  Tgl.".toRojbDate($kw->tgl), 0, 1);

    $pdf->Output("I", "KUITANSI-".$no_kuitansi.".pdf");
});
// END AKTIVASI RUTIN

// START AKTIVASI SO
Flight::route("/unit/aktivasi/so", function() {
    $header = [
        "title" => "Unit Aktivasi SO " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3221]
    ];

    // load user plta
    $data = [
        "order" => "full_name",
        "sort" => "asc",
        "search_by" => "role",
        "token" => $_SESSION["token"]
    ];
    $responseUser = getApiResponse(WEB_API . "user/search/1/1000/PLTA", $data);

    // load existing so
    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = [
        "dataUser" => $responseUser->result->data, 
        "dataSo" => $responseSo->result->data, 
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "key" => $key,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y")
    ];
    renderView($header, $dataOutput, "unit/aktivasi/so.php");
});
// END AKTIVASI SO

// START AKTIVASI JASA SO
Flight::route("/unit/aktivasi/so/jasa", function() {
    $header = [
        "title" => "Unit Aktivasi Jasa SO " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3222]
    ];

    // load existing so
    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = [
        "dataSo" => $responseSo->result->data, 
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "key" => $key
    ];
    renderView($header, $dataOutput, "unit/aktivasi/jasa_so.php");
});

Flight::route("/unit/aktivasi/so/jasa/input/@no_so", function($no_so) use ($request) {
    // check dulu apakah no_so ada (atau no_so telah memiliki jasa so -> harus ditanyakan)
    $no_so = str_replace("-", "/", $no_so);
    $data = ["key" => $no_so, "search_by" => "no_so", "order" => "no_so", "sort" => "asc", 
        "from_date" => "", "to_date" => "", "token" => $_SESSION["token"]];
    $responseNoSo = getApiResponse(WEB_API . "aktivasi/so/search_by/1/100", $data);
    if ($responseNoSo->result->total_all == 0)
        Flight::redirect("/unit/aktivasi/so/jasa");

    $header = [
        "title" => "Unit Aktivasi Jasa SO Input " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3222]
    ];

    // get autonumber aktivasi so jasa
    $data = ["type" => "AKTIVASI-SO-JASA", "token" => $_SESSION["token"]];
    $responseNoJasa = getApiResponse(WEB_API . "util/autonumber/create", $data);
    $noBuktiJasaEx = explode("/", $responseNoJasa->result->auto_number);
    $noKuitansiSuffix = $noBuktiJasaEx[0] . "/" . $noBuktiJasaEx[1];

    // get no kuitansi
    $data = ["type" => "KUITANSI-AKTIVASI-SO-JASA", "no_bukti" => $noKuitansiSuffix, "token" => $_SESSION["token"]];
    $responseNoKuitansi = getApiResponse(WEB_API . "util/autonumber/create", $data);

    // load referensi jenis jasa
    $data = ["order" => "kode", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseJasa = getApiResponse(WEB_API . "referensi/jasa/search/1/150/_", $data);

    $dataOutput = [
        "dataJasa" => $responseJasa->result->data,
        "no_bukti" => $responseNoJasa->result->auto_number,
        "no_kuitansi" => $responseNoKuitansi->result->auto_number,
        "no_so" => $no_so,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "admin_kuitansi" => $_SESSION["name"]
    ];
    renderView($header, $dataOutput, "unit/aktivasi/jasa_so_input.php");
});

Flight::route("/unit/aktivasi/so/jasa/do_input", function() {
    $input = $_POST;

    $data = [
        "no_so" => $input["no_so"],
        "no_bukti" => $input["no_bukti"],
        "tgl" => $input["tgl"],
        "no_kuitansi" => ($input["no_kuitansi"] == "0") ? "" : $input["no_kuitansi"],
        "tgl_kuitansi" => ($input["tgl_kuitansi"] == "0") ? "" : $input["tgl_kuitansi"],
        "admin_kuitansi" => ($input["admin_kuitansi"] == "0") ? "" : $input["admin_kuitansi"],
        "penerima_kuitansi" => ($input["penerima_kuitansi"] == "0") ? "" : $input["penerima_kuitansi"],
        "id_kuitansi" => ($input["id_kuitansi"] == "0") ? "" : $input["id_kuitansi"],
        "detail" => [[
            "kode_jasa" => $input["kode_jasa"],
            "jenis_jasa" => $input["jenis_jasa"],
            "deskripsi" => "",
            "pelaksana" => $input["pelaksana"],
            "nilai" => $input["biaya"]
        ]],
        "token" => $_SESSION["token"]
    ];

    $responseJasa = getApiResponse(WEB_API . "aktivasi/so/jasa/create", $data);
    echo json_encode($responseJasa);
});

Flight::route("/unit/aktivasi/so/jasa/pengeluaran", function() {
    $header = [
        "title" => "Unit Aktivasi Jasa SO Pengeluaran " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3222]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "no_bukti",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];

    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];

    $response = getApiResponse(WEB_API . "aktivasi/so/jasa/search/1/150/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = ["data" => $response->result->data, "key" => $key, "range_date" => $range_date, "from_date" => $from_date, "to_date" => $to_date];
    renderView($header, $dataOutput, "unit/aktivasi/jasa_so_pengeluaran.php");
});

Flight::route("/unit/aktivasi/so/jasa/kuitansi/@no_kuitansi", function($no_kuitansi) {
    // get detail kuitansi
    $noKuitansi = strReplaceFirst("-", "/", $no_kuitansi);
    $data = [
        "key" => $noKuitansi,
        "order" => "no_bukti",
        "sort" => "asc",
        "search_by" => "no_kuitansi",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $responseKuitansi = getApiResponse(WEB_API . "aktivasi/so/jasa/search_by/1/1", $data);
    $kw = $responseKuitansi->result->data[0];

    $total = 0;
    $untukPembayaran = "";
    foreach ($kw->detail as $id => $row)
    {
        $total += (int) $row->nilai;
        $untukPembayaran .= $row->deskripsi." Rp. ".toRupiah($row->nilai)." ";
    }

    $pdf = new FPDF();
    $pdf->AddPage("L", "A5");
    $pdf->setAuthor("Aditia Rahman");
    $pdf->SetFont("Arial", "B", 12);
    $pdf->Image("themes/img/gsp-logo-long.jpg", 10, 10, 100);
    $pdf->Cell(120);

    $pdf->Cell(0, 5, "KUITANSI GSP", 0, 1);
    $pdf->Cell(120);
    $pdf->SetFont("Arial", "",10);
    $pdf->Cell(0, 10, "No. ".$noKuitansi, 0, 1);

    $pdf->Cell(10, 3, "", 0, 1);
    $pdf->Cell(32, 6, "Sudah terima dari", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": PT GERBANG SINERGI PRIMA", 0, 1);

    $pdf->Cell(32, 6, "Banyaknya uang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": Rp. ".toRupiah($total), 0, 1);

    $pdf->Cell(32, 6, "Terbilang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ":".strtoupper(rpTerbilang($total))." RUPIAH", 0, 1);

    // $pdf->Cell(32, 7, "Untuk pembayaran", 0, 0);
    // $pdf->Cell(5);
    // $pdf->Cell(85, 7, ": ".$untukPembayaran, 0, 1);

    foreach ($kw->detail as $id => $row)
    {
        if ($id == 0)
            $pdf->Cell(32, 6, "Untuk pembayaran", 0, 0);
        else
            $pdf->Cell(32, 6, "", 0, 0);

        $pdf->Cell(5);
        $pdf->Cell(85, 6, ": ".$row->jenis_jasa." Rp. ".toRupiah($row->nilai), 0, 1);
    }

    // separator
    $pdf->Cell(32, 15, "", 0, 1);

    $pdf->Cell(32, 6, "Tanda Tangan", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 6, "__________________________", 0, 0);

    $pdf->SetFont("Arial", "", 9);
    $pdf->Cell(1);
    $pdf->Cell(82, 5, "Saya menjamin kebenaran dan bertanggung jawab", "TLR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "sepenuhnya atas seluruh informasi yang terdapat dalam", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "kuitansi ini.", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 3, "", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "Bendahara	: WAHYU (_____________________)", "LRB", 1);

    $pdf->Cell(32, 5, "Nama Penerima", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5,": ".$kw->penerima_kuitansi, 0, 1);

    $pdf->Cell(32, 5, "ID", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5, ": ".$kw->id_kuitansi, 0, 1);

    $pdf->Cell(10, 1, "", 0, 1);
    $pdf->Cell(10, 1, '________________________________________________________________________________________________________', 0, 1);

    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(125);
    $pdf->Cell(82, 6, "NO. BUKTI: ".$kw->no_bukti."  Tgl.".toRojbDate($kw->tgl), 0, 1);

    $pdf->Output("I", "KUITANSI-".$no_kuitansi.".pdf");
});
// END AKTIVASI JASA SO

// START AKTIVASI BIAYA LAIN SO
Flight::route("/unit/aktivasi/so/biaya_lain", function() {
    $header = [
        "title" => "Unit Aktivasi Biaya Lain SO " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3223]
    ];

    // load existing so
    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = [
        "dataSo" => $responseSo->result->data, 
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "key" => $key
    ];
    renderView($header, $dataOutput, "unit/aktivasi/biaya_lain_so.php");
});

Flight::route("/unit/aktivasi/so/biaya_lain/input/@no_so", function($no_so) use ($request) {
    $no_so = str_replace("-", "/", $no_so);
    $data = ["key" => $no_so, "search_by" => "no_so", "order" => "no_so", "sort" => "asc", 
        "from_date" => "", "to_date" => "", "token" => $_SESSION["token"]];
    $responseNoSo = getApiResponse(WEB_API . "aktivasi/so/search_by/1/100", $data);
    if ($responseNoSo->result->total_all == 0)
        Flight::redirect("/unit/aktivasi/so/biaya_lain");

    $header = [
        "title" => "Unit Aktivasi Jasa Biaya Lain Input " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3223]
    ];

    // get autonumber aktivasi so jasa
    $data = ["type" => "AKTIVASI-SO-BIAYA-LAIN", "token" => $_SESSION["token"]];
    $responseNoBiaya = getApiResponse(WEB_API . "util/autonumber/create", $data);
    $noBuktiJasaEx = explode("/", $responseNoBiaya->result->auto_number);
    $noKuitansiSuffix = $noBuktiJasaEx[0] . "/" . $noBuktiJasaEx[1];

    // get no kuitansi
    $data = ["type" => "KUITANSI-AKTIVASI-SO-BIAYA-LAIN", "no_bukti" => $noKuitansiSuffix, "token" => $_SESSION["token"]];
    $responseNoKuitansi = getApiResponse(WEB_API . "util/autonumber/create", $data);

    // load referensi jenis jasa
    $data = ["order" => "kode", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseBiaya = getApiResponse(WEB_API . "referensi/biaya/search/1/150/_", $data);

    $dataOutput = [
        "dataBiaya" => $responseBiaya->result->data,
        "no_bukti" => $responseNoBiaya->result->auto_number,
        "no_kuitansi" => $responseNoKuitansi->result->auto_number,
        "no_so" => $no_so,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "admin_kuitansi" => $_SESSION["name"]
    ];
    renderView($header, $dataOutput, "unit/aktivasi/biaya_lain_so_input.php");
});

Flight::route("/unit/aktivasi/so/biaya_lain/do_input", function() {
    $input = $_POST;
    
    $count = 0;
    $detail = [];

    switch ($input["kode_biaya"])
    {
        case "B0100":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", Q: " . $input["qty"][$idx]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$idx]);
                $count++;
            }
            break;
        
        case "B0200":
            foreach ($input["kendaraan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO. PLAT: " . $input["no_plat"][$count] . ", LTR: " . $input["ltr"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0300":
            foreach ($input["ormas"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", NO. AR: " . $input["no_ar"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0400":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
        case "B0500":
            foreach ($input["pulsa"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO. KARTU: " . $input["no_hp"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0600":
            foreach ($input["listrik"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0700":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0800":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0900":
            if ($input["kendaraan"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("KENDARAAN, NO. PLAT: " . $input["no_plat"] . ", KET: " . $input["keterangan_1"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_1"]) + (int) str_replace(".", "", $input["material_rp_1"]);
                $count++;
            }

            if ($input["bangunan"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("BANGUNAN, REG: " . $input["regional_1"] . ", KET: " . $input["keterangan_2"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_2"]) + (int) str_replace(".", "", $input["material_rp_2"]);
                $count++;
            }

            if ($input["lainnya"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("LAINNYA, REG: " . $input["regional_2"] . ", KET: " . $input["keterangan_3"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_3"]) + (int) str_replace(".", "", $input["material_rp_3"]);
                $count++;
            }
            break;

        case "B1000":                
            foreach ($input["no_ar"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NO. AR: " . $row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1100":
            foreach ($input["parkir"] as $idx => $row)
            {
                switch ($row[0])
                {
                    case "PARKIR":
                        $detail[$count]["deskripsi"] = strtoupper($row[0]);
                        break;

                    case "TOL":
                        $detail[$count]["deskripsi"] = strtoupper($row[0] . ", LOK: " . $input["lokasi_tol"][$count]);
                        break;

                    case "E-TOL":
                        $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO: " . $input["no_etoll"][$count]);
                        break;
                }

                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
    
        case "B1200":
            foreach ($input["nama"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NAMA: " . $row . ", INST: " . $input["inst"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1300":
            foreach ($input["sewa"] as $idx => $row)
            {
                if ($row != "0")
                {
                    $desk = strtoupper("DET: " . $input["detail"][$count] . ", PER: " . $input["periode"][$count] . ", PEM: " . $input["pemilik"][$count]);
                    $detail[$count]["deskripsi"] = $desk;
                    $detail[$count]["nilai"] = str_replace(".", "", $input["rp"][$count]);
                }
                $count++;
            }
            break;

        case "B1400":
            $desk = strtoupper($input["jenis_jasa"]. ", " . $input["jenis_biaya"] . ", " . $input["penyedia_jasa"] . ", " . $input["no_ktp"]);
            $detail[$count]["deskripsi"] = $desk;
            $detail[$count]["nilai"] = str_replace(".", "", $input["biaya_jasa"]);
            break;

        case "B1500":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1600":
            foreach ($input["nama"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NAMA: " . $row . ", MOJU: " . $input["moju"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1700":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", SN: " . $input["sn"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
    }

    $data = [
        "no_so" => $input["no_so"],
        "no_bukti" => $input["no_bukti"],
        "tgl" => $input["tgl"],
        "kode_biaya" => $input["kode_biaya"],
        "jenis_biaya" => $input["jenis_biaya"],
        "no_kuitansi" => ($input["no_kuitansi"] == "0") ? "" : $input["no_kuitansi"],
        "tgl_kuitansi" => ($input["tgl_kuitansi"] == "0") ? "" : $input["tgl_kuitansi"],
        "admin_kuitansi" => ($input["admin_kuitansi"] == "0") ? "" : $input["admin_kuitansi"],
        "penerima_kuitansi" => ($input["penerima_kuitansi"] == "0") ? "" : $input["penerima_kuitansi"],
        "id_kuitansi" => ($input["id_kuitansi"] == "0") ? "" : $input["id_kuitansi"],
        "detail" => $detail,
        "token" => $_SESSION["token"]
    ];

    $response = getApiResponse(WEB_API . "aktivasi/so/biaya_lain/create", $data);
    echo json_encode($response);
});

Flight::route("/unit/aktivasi/so/biaya_lain/load_no_bukti_baru", function() {
    // get autonumber aktivasi rutin
    $data = ["type" => "AKTIVASI-SO-BIAYA-LAIN", "token" => $_SESSION["token"]];
    $responseNo = getApiResponse(WEB_API . "util/autonumber/create", $data);
    $noBuktiAktivasiEx = explode("/", $responseNo->result->auto_number);
    $noKuitansiSuffix = $noBuktiAktivasiEx[0] . "/" . $noBuktiAktivasiEx[1];

    // get no kuitansi
    $data = ["type" => "KUITANSI-AKTIVASI-SO-BIAYA-LAIN", "no_bukti" => $noKuitansiSuffix, "token" => $_SESSION["token"]];
    $responseNoKuitansi = getApiResponse(WEB_API . "util/autonumber/create", $data);

    $data = [
        "no_bukti" => $responseNo->result->auto_number,
        "no_kuitansi" => $responseNoKuitansi->result->auto_number,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"),
    ];

    echo json_encode($data);
});

Flight::route("/unit/aktivasi/so/biaya_lain/pengeluaran", function() {
    $header = [
        "title" => "Unit Aktivasi Jasa Biaya Lain Pengeluaran " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3223]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "no_bukti",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];

    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];

    $response = getApiResponse(WEB_API . "aktivasi/so/biaya_lain/search/1/150/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = ["data" => $response->result->data, "key" => $key, "range_date" => $range_date, "from_date" => $from_date, "to_date" => $to_date];
    renderView($header, $dataOutput, "unit/aktivasi/biaya_lain_so_pengeluaran.php");
});

Flight::route("/unit/aktivasi/so/biaya_lain/kuitansi/@no_kuitansi", function($no_kuitansi) {
    // get detail kuitansi
    $noKuitansi = strReplaceFirst("-", "/", $no_kuitansi);
    $data = [
        "key" => $noKuitansi,
        "order" => "no_bukti",
        "sort" => "asc",
        "search_by" => "no_kuitansi",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $responseKuitansi = getApiResponse(WEB_API . "aktivasi/so/biaya_lain/search_by/1/1", $data);
    $kw = $responseKuitansi->result->data[0];

    $total = 0;
    $untukPembayaran = "";
    foreach ($kw->detail as $id => $row)
    {
        $total += (int) $row->nilai;
        $untukPembayaran .= $row->deskripsi." Rp. ".toRupiah($row->nilai)." ";
    }

    $pdf = new FPDF();
    $pdf->AddPage("L", "A5");
    $pdf->setAuthor("Aditia Rahman");
    $pdf->SetFont("Arial", "B", 12);
    $pdf->Image("themes/img/gsp-logo-long.jpg", 10, 10, 100);
    $pdf->Cell(120);

    $pdf->Cell(0, 5, "KUITANSI GSP", 0, 1);
    $pdf->Cell(120);
    $pdf->SetFont("Arial", "",10);
    $pdf->Cell(0, 10, "No. ".$noKuitansi, 0, 1);

    $pdf->Cell(10, 3, "", 0, 1);
    $pdf->Cell(32, 6, "Sudah terima dari", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": PT GERBANG SINERGI PRIMA", 0, 1);

    $pdf->Cell(32, 6, "Banyaknya uang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": Rp. ".toRupiah($total), 0, 1);

    $pdf->Cell(32, 6, "Terbilang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ":".strtoupper(rpTerbilang($total))." RUPIAH", 0, 1);

    // $pdf->Cell(32, 7, "Untuk pembayaran", 0, 0);
    // $pdf->Cell(5);
    // $pdf->Cell(85, 7, ": ".$untukPembayaran, 0, 1);

    foreach ($kw->detail as $id => $row)
    {
        if ($id == 0)
            $pdf->Cell(32, 6, "Untuk pembayaran", 0, 0);
        else
            $pdf->Cell(32, 6, "", 0, 0);

        $pdf->Cell(5);
        $pdf->Cell(85, 6, ": ".$row->deskripsi." Rp. ".toRupiah($row->nilai), 0, 1);
    }

    // separator
    $pdf->Cell(32, 15, "", 0, 1);

    $pdf->Cell(32, 6, "Tanda Tangan", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 6, "__________________________", 0, 0);

    $pdf->SetFont("Arial", "", 9);
    $pdf->Cell(1);
    $pdf->Cell(82, 5, "Saya menjamin kebenaran dan bertanggung jawab", "TLR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "sepenuhnya atas seluruh informasi yang terdapat dalam", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "kuitansi ini.", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 3, "", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "Bendahara	: WAHYU (_____________________)", "LRB", 1);

    $pdf->Cell(32, 5, "Nama Penerima", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5,": ".$kw->penerima_kuitansi, 0, 1);

    $pdf->Cell(32, 5, "ID", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5, ": ".$kw->id_kuitansi, 0, 1);

    $pdf->Cell(10, 1, "", 0, 1);
    $pdf->Cell(10, 1, '________________________________________________________________________________________________________', 0, 1);

    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(125);
    $pdf->Cell(82, 6, "NO. BUKTI: ".$kw->no_bukti."  Tgl.".toRojbDate($kw->tgl), 0, 1);

    $pdf->Output("I", "KUITANSI-".$no_kuitansi.".pdf");
});
// END AKTIVASI BIAYA LAIN SO

// START AKTIVASI PKB SO
Flight::route("/unit/aktivasi/so/pkb", function() {
    $header = [
        "title" => "Unit Aktivasi PKB SO " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3224]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    // load referensi khs
    $responseKhs = getApiResponse(WEB_API . "referensi/khs/search/1/1500/_", $data);

    // load referensi material
    $data["order"] = "kode";
    $responseMaterial = getApiResponse(WEB_API . "referensi/material/search/1/1500/_", $data);

    $dataOutput = [
        "dataKhs" => $responseKhs->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "dataSo" => $responseSo->result->data, 
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];

    renderView($header, $dataOutput, "unit/aktivasi/pkb_so.php");
});

Flight::route("/unit/aktivasi/so/pkb/view/@no_pkb", function($no_pkb) {
    $header = [
        "title" => "Unit Aktivasi PKB SO Detail " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3224]
    ];

    $noPkb = str_replace("_", "/", $no_pkb);

    $data = [
        "key" => $noPkb,
        "order" => "no_so",
        "sort" => "asc",
        "search_by" => "pkb_no_bukti",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $responseSoPkb = getApiResponse(WEB_API . "aktivasi/so/search_by/1/1", $data);

    $dataOutput = [
        "dataSoPkb" => $responseSoPkb->result->data,
        "range_date" => "", 
        "from_date" => "", 
        "to_date" => "",
        "key" => "",
        "no_pkb" => $noPkb
    ];

    renderView($header, $dataOutput, "unit/aktivasi/pkb_so_detail.php");
});

Flight::route("/unit/aktivasi/so/pkb/detail", function() {
    $header = [
        "title" => "Unit Aktivasi PKB SO Detail " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3224]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    
    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $responseSoPkb = getApiResponse(WEB_API . "aktivasi/so/pkb/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;

    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = [
        "dataSoPkb" => $responseSoPkb->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "key" => $key,
        "no_pkb" => ""
    ];

    renderView($header, $dataOutput, "unit/aktivasi/pkb_so_detail.php");
});
// END AKTIVASI SO PKB

// START AKTIVASI WASPANG SO
Flight::route("/unit/aktivasi/so/waspang", function() {
    $header = [
        "title" => "Unit Aktivasi Waspang SO Detail " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3225]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = [
        "dataSo" => $responseSo->result->data, 
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "key" => $key,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"),
        "waspang_name" => $_SESSION["name"]
    ];

    renderView($header, $dataOutput, "unit/aktivasi/waspang_so.php");
});

Flight::route("/unit/aktivasi/so/waspang/ba/@no_ba", function($no_ba) {
    
    $noBa = str_replace("_", "/", $no_ba);
    $data = [
        "key" => $noBa,
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $responseSoWaspang = getApiResponse(WEB_API . "aktivasi/so/waspang/search/1/1/", $data);
    $waspang = $responseSoWaspang->result->data[0];    

    $pdf = new PDF_MC_Table();
    $pdf->AddPage("L", "A5");
    $pdf->setAuthor("Aditia Rahman");
    $pdf->SetFont("Arial", "B", 12);
    $pdf->Image("themes/img/gsp-logo-long.jpg", 10, 10, 100);
    $pdf->Cell(120);

    $pdf->Cell(0, 5, "BA WASPANG", 0, 1);
    $pdf->Cell(120);
    $pdf->SetFont("Arial", "",10);
    $pdf->Cell(0, 10, "No. ".$noBa, 0, 1);

    $pdf->Cell(10, 3, "", 0, 1);
    $pdf->Cell(32, 6, "Hasil pemeriksaan atas penyelesaian SO ".$waspang->no_so." ".$waspang->pekerjaan." sesuai dengan", 0, 1);
    $pdf->Cell(10, 0, "", 0, 1);
    $pdf->Cell(32, 6, $waspang->pkb_no_bukti." ".toRojbDate($waspang->tgl).", dengan rincian sebagai berikut:", 0, 1);

    $pdf->Cell(10, 3, "", 0, 1);
    //Table with 20 rows and 4 columns
    $pdf->SetWidths([110, 25, 25, 25]);
    $pdf->SetFont("Arial", "B",10);
    $pdf->Row([
        "                                       JENIS MATERIAL", 
        "    QTY-OUT", 
        "   REALISASI", 
        "     SELISIH"]);

    $pdf->SetFont("Arial", "",10);
    $jmlQty = 0;
    foreach ($waspang->material as $idx => $material)
    {
        $jmlQty += (int) $material->qty;
        $pdf->Row([
            $material->kode_material." ".$material->jenis_material,
            "          ".$material->qty,
            "",
            ""
        ]);
    }

    $pdf->SetFont("Arial", "B",10);
    $pdf->Row(["                                            JUMLAH", "          ".$jmlQty, "", ""]);

    $pdf->SetFont("Arial", "",10);
    $pdf->Cell(10, 6, "", 0, 1);

    $pdf->Cell(20, 12, "Catatan", 0, 0);
    $pdf->Cell(10, 12, "___________________________________________________________________________________", 0, 1);
    $pdf->Cell(20, 12, "", 0, 0);
    $pdf->Cell(10, 12, "___________________________________________________________________________________", 0, 1);

    $pdf->Cell(20, 12, "WASPANG:", 0, 0);
    $pdf->Cell(10, 12, $waspang->waspang_nama, 0, 0);
    
    $pdf->Output("I", "NOBA-".$noBa.".pdf");
});
// END AKTIVASI WASPANG SO

// START AKTIVASI PKT SO
Flight::route("/unit/aktivasi/so/pkt", function() {
     $header = [
        "title" => "Unit Aktivasi PKT SO " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3226]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    // load referensi khs
    $responseKhs = getApiResponse(WEB_API . "referensi/khs/search/1/1500/_", $data);

    // load referensi material
    $responseMaterial = getApiResponse(WEB_API . "referensi/material/search/1/1500/_", $data);

    $dataOutput = [
        "dataKhs" => $responseKhs->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "dataSo" => $responseSo->result->data, 
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];

    renderView($header, $dataOutput, "unit/aktivasi/pkt_so.php");
});

Flight::route("/unit/aktivasi/so/pkt/hapus_gr", function() {
    $no_so = $_POST["no_so"];
    $file_gr = $_POST["file_gr"];

    // delete from disk
    $file_gr_path = str_replace(WEB_URL, "", $file_gr);
    @unlink($file_gr_path);

    $dataUpload = [
        "no_so" => $_POST["no_so"],
        "token" => $_SESSION["token"]
    ];

    $responseGr = getApiResponse(WEB_API . "aktivasi/so/pkt/hapus_gr", $dataUpload);
    echo json_encode($responseGr);
});

Flight::route("/unit/aktivasi/so/pkt/detail", function() {

    $file_upload_msg = "";

    if (isset($_POST["btnUploadGr"]))
    {
        $uploaddir = "uploads/pkt_gr/";
        $uploadfile = $uploaddir . date("Ymd") . randomString(4) . "_" . cleanString(basename($_FILES["file_gr"]["name"]));

        if (move_uploaded_file($_FILES["file_gr"]["tmp_name"], $uploadfile)) 
        {
            // send to api
            $dataUpload = [
                "no_so" => $_POST["no_so"],
                "pkt_no_bukti" => $_POST["pkt_no_bukti"],
                "pkt_gr_file" => WEB_URL . $uploadfile,
                "token" => $_SESSION["token"]
            ];

            $responseGr = getApiResponse(WEB_API . "aktivasi/so/pkt/upload_gr", $dataUpload);
            $file_upload_msg = $responseGr->message;
        }
    }

    $header = [
        "title" => "Unit Aktivasi PKT SO Detail" . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3226]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/pkt/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = [
        "dataSo" => $responseSo->result->data, 
        "fileUploadMsg" => $file_upload_msg,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];

    renderView($header, $dataOutput, "unit/aktivasi/pkt_so_detail.php");
});

Flight::route("/unit/aktivasi/so/pkt/material/detail", function() use ($request) {

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "key" => "",
        "search_by" => "pkb_no_bukti",
        "order" => "no_so",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/material/search_by/1/1000/", $data);
    
    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = [
        "dataSo" => $responseSo->result->data, 
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];
    renderView($request->header, $dataOutput, "unit/aktivasi/pkt_so_material_detail.php");
});

Flight::route("/unit/aktivasi/so/pkt/view/@no_pkt", function($no_pkt) {
    $header = [
        "title" => "Unit Aktivasi PKT SO Detail " . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3226]
    ];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSo = getApiResponse(WEB_API . "aktivasi/so/search/1/1000/" . $no_pkt, $data);

    $dataOutput = [
        "dataSo" => $responseSo->result->data, 
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];

    renderView($header, $dataOutput, "unit/aktivasi/pkt_so_view.php");
});

Flight::route("/unit/aktivasi/so/pkt/view_by_pkb/@no_pkb", function($no_pkb) {
    $header = [
        "title" => "Unit Aktivasi PKT SO Detail By PKB" . APP_NAME,
        "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3226]
    ];

    $noPkb = str_replace("_", "/", $no_pkb);

    $data = [
        "key" => $noPkb,
        "order" => "no_so",
        "sort" => "asc",
        "search_by" => "pkb_no_bukti",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $responseSoPkb = getApiResponse(WEB_API . "aktivasi/so/search_by/1/1", $data);

    $dataOutput = [
        "dataSo" => $responseSoPkb->result->data,
        "fileUploadMsg" => "",
        "range_date" => "", 
        "from_date" => "", 
        "to_date" => "",
        "key" => "",
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "no_pkb" => $noPkb
    ];

    renderView($header, $dataOutput, "unit/aktivasi/pkt_so_detail.php");
});
// END AKTIVASI PKT SO

// START SERPO RUTIN
Flight::route("/unit/serpo/rutin", function() {
    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "no_bukti",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];

    $header = [
        "title" => "Unit Serpo Rutin " . APP_NAME,
        "menu" => ["first" => 3, "second" => 31, "third" => 311, "fourth" => 0]
    ];

    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];

    $response = getApiResponse(WEB_API . "serpo/rutin/search/1/150/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    $dataOutput = ["data" => $response->result->data, "key" => $key, "range_date" => $range_date, "from_date" => $from_date, "to_date" => $to_date];
    renderView($header, $dataOutput, "unit/serpo/rutin.php");
});

Flight::route("/unit/serpo/input", function() {
    $header = [
        "title" => "Unit Serpo Rutin Input " . APP_NAME,
        "menu" => ["first" => 3, "second" => 31, "third" => 311, "fourth" => 0]
    ];

    // get autonumber aktivasi rutin
    $data = ["type" => "SERPO-RUTIN", "token" => $_SESSION["token"]];
    $responseNo = getApiResponse(WEB_API . "util/autonumber/create", $data);
    $noBuktiAktivasiEx = explode("/", $responseNo->result->auto_number);
    $noKuitansiSuffix = $noBuktiAktivasiEx[0] . "/" . $noBuktiAktivasiEx[1];

    // get no kuitansi
    $data = ["type" => "KUITANSI-SERPO-RUTIN", "no_bukti" => $noKuitansiSuffix, "token" => $_SESSION["token"]];
    $responseNoKuitansi = getApiResponse(WEB_API . "util/autonumber/create", $data);

    // get jenis biaya
    $data = ["order" => "kode", "sort" => "asc", "token" => $_SESSION["token"]];
    $responseBiaya = getApiResponse(WEB_API . "referensi/biaya/search/1/150/_", $data);

    $dataOutput = [
        "biaya" => $responseBiaya->result->data, 
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "no_bukti" => $responseNo->result->auto_number,
        "no_kuitansi" => $responseNoKuitansi->result->auto_number,
        "admin_kuitansi" => $_SESSION["name"]
    ];
    renderView($header, $dataOutput, "unit/serpo/input.php");
});

Flight::route("/unit/serpo/do_input", function() {
    $input = $_POST;
    
    $count = 0;
    $detail = [];

    switch ($input["kode_biaya"])
    {
        case "B0100":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", Q: " . $input["qty"][$idx]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$idx]);
                $count++;
            }
            break;
        
        case "B0200":
            foreach ($input["kendaraan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO. PLAT: " . $input["no_plat"][$count] . ", LTR: " . $input["ltr"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0300":
            foreach ($input["ormas"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", NO. AR: " . $input["no_ar"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0400":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
        case "B0500":
            foreach ($input["pulsa"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO. KARTU: " . $input["no_hp"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0600":
            foreach ($input["listrik"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row[0] . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0700":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0800":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B0900":
            if ($input["kendaraan"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("KENDARAAN, NO. PLAT: " . $input["no_plat"] . ", KET: " . $input["keterangan_1"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_1"]) + (int) str_replace(".", "", $input["material_rp_1"]);
                $count++;
            }

            if ($input["bangunan"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("BANGUNAN, REG: " . $input["regional_1"] . ", KET: " . $input["keterangan_2"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_2"]) + (int) str_replace(".", "", $input["material_rp_2"]);
                $count++;
            }

            if ($input["lainnya"] != NULL)
            {
                $detail[$count]["deskripsi"] = strtoupper("LAINNYA, REG: " . $input["regional_2"] . ", KET: " . $input["keterangan_3"] . ", JASA/MATERIAL");
                $detail[$count]["nilai"] = (int) str_replace(".", "", $input["jasa_rp_3"]) + (int) str_replace(".", "", $input["material_rp_3"]);
                $count++;
            }
            break;

        case "B1000":                
            foreach ($input["no_ar"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NO. AR: " . $row . ", REG: " . $input["regional"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1100":
            foreach ($input["parkir"] as $idx => $row)
            {
                switch ($row[0])
                {
                    case "PARKIR":
                        $detail[$count]["deskripsi"] = strtoupper($row[0]);
                        break;

                    case "TOL":
                        $detail[$count]["deskripsi"] = strtoupper($row[0] . ", LOK: " . $input["lokasi_tol"][$count]);
                        break;

                    case "E-TOL":
                        $detail[$count]["deskripsi"] = strtoupper($row[0] . ", NO: " . $input["no_etoll"][$count]);
                        break;
                }

                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
    
        case "B1200":
            foreach ($input["nama"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NAMA: " . $row . ", INST: " . $input["inst"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1300":
            foreach ($input["sewa"] as $idx => $row)
            {
                if ($row != "0")
                {
                    $desk = strtoupper("DET: " . $input["detail"][$count] . ", PER: " . $input["periode"][$count] . ", PEM: " . $input["pemilik"][$count]);
                    $detail[$count]["deskripsi"] = $desk;
                    $detail[$count]["nilai"] = str_replace(".", "", $input["rp"][$count]);
                }
                $count++;
            }
            break;

        case "B1400":
            $desk = strtoupper($input["jenis_jasa"]. ", " . $input["jenis_biaya"] . ", " . $input["penyedia_jasa"] . ", " . $input["no_ktp"]);
            $detail[$count]["deskripsi"] = $desk;
            $detail[$count]["nilai"] = str_replace(".", "", $input["biaya_jasa"]);
            break;

        case "B1500":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1600":
            foreach ($input["nama"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper("NAMA: " . $row . ", MOJU: " . $input["moju"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;

        case "B1700":
            foreach ($input["keterangan"] as $idx => $row)
            {
                $detail[$count]["deskripsi"] = strtoupper($row . ", SN: " . $input["sn"][$count]);
                $detail[$count]["nilai"] = str_replace(".", "", $input["harga"][$count]);
                $count++;
            }
            break;
    }

    $data = [
        "no_bukti" => $input["no_bukti"],
        "tgl" => $input["tgl"],
        "kode_biaya" => $input["kode_biaya"],
        "jenis_biaya" => $input["jenis_biaya"],
        "no_kuitansi" => ($input["no_kuitansi"] == "0") ? "" : $input["no_kuitansi"],
        "tgl_kuitansi" => ($input["tgl_kuitansi"] == "0") ? "" : $input["tgl_kuitansi"],
        "admin_kuitansi" => ($input["admin_kuitansi"] == "0") ? "" : $input["admin_kuitansi"],
        "penerima_kuitansi" => ($input["penerima_kuitansi"] == "0") ? "" : $input["penerima_kuitansi"],
        "id_kuitansi" => ($input["id_kuitansi"] == "0") ? "" : $input["id_kuitansi"],
        "detail" => $detail,
        "token" => $_SESSION["token"]
    ];

    $response = getApiResponse(WEB_API . "serpo/rutin/create", $data);
    echo json_encode($response);
});

Flight::route("/unit/serpo/load_no_bukti_baru", function() {
    // get autonumber aktivasi rutin
    $data = ["type" => "SERPO-RUTIN", "token" => $_SESSION["token"]];
    $responseNo = getApiResponse(WEB_API . "util/autonumber/create", $data);
    $noBuktiAktivasiEx = explode("/", $responseNo->result->auto_number);
    $noKuitansiSuffix = $noBuktiAktivasiEx[0] . "/" . $noBuktiAktivasiEx[1];

    // get no kuitansi
    $data = ["type" => "KUITANSI-SERPO-RUTIN", "no_bukti" => $noKuitansiSuffix, "token" => $_SESSION["token"]];
    $responseNoKuitansi = getApiResponse(WEB_API . "util/autonumber/create", $data);

    $data = [
        "no_bukti" => $responseNo->result->auto_number,
        "no_kuitansi" => $responseNoKuitansi->result->auto_number,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"),
    ];

    echo json_encode($data);
});

Flight::route("/unit/serpo/kuitansi/@no_kuitansi", function($no_kuitansi) {
    // get detail kuitansi
    $noKuitansi = strReplaceFirst("-", "/", $no_kuitansi);
    $data = [
        "key" => $noKuitansi,
        "order" => "no_bukti",
        "sort" => "asc",
        "search_by" => "no_kuitansi",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];
    $responseKuitansi = getApiResponse(WEB_API . "serpo/rutin/search_by/1/1", $data);

    $kw = $responseKuitansi->result->data[0];

    $total = 0;
    $untukPembayaran = "";
    foreach ($kw->detail as $id => $row)
    {
        $total += (int) $row->nilai;
        $untukPembayaran .= $row->deskripsi." Rp. ".toRupiah($row->nilai)." ";
    }

    $pdf = new FPDF();
    $pdf->AddPage("L", "A5");
    $pdf->setAuthor("Aditia Rahman");
    $pdf->SetFont("Arial", "B", 12);
    $pdf->Image("themes/img/gsp-logo-long.jpg", 10, 10, 100);
    $pdf->Cell(120);

    $pdf->Cell(0, 5, "KUITANSI GSP", 0, 1);
    $pdf->Cell(120);
    $pdf->SetFont("Arial", "",10);
    $pdf->Cell(0, 10, "No. ".$noKuitansi, 0, 1);

    $pdf->Cell(10, 3, "", 0, 1);
    $pdf->Cell(32, 6, "Sudah terima dari", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": PT GERBANG SINERGI PRIMA", 0, 1);

    $pdf->Cell(32, 6, "Banyaknya uang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": Rp. ".toRupiah($total), 0, 1);

    $pdf->Cell(32, 6, "Terbilang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ":".strtoupper(rpTerbilang($total))." RUPIAH", 0, 1);

    // $pdf->Cell(32, 7, "Untuk pembayaran", 0, 0);
    // $pdf->Cell(5);
    // $pdf->Cell(85, 7, ": ".$untukPembayaran, 0, 1);

    foreach ($kw->detail as $id => $row)
    {
        if ($id == 0)
            $pdf->Cell(32, 6, "Untuk pembayaran", 0, 0);
        else
            $pdf->Cell(32, 6, "", 0, 0);

        $pdf->Cell(5);
        $pdf->Cell(85, 6, ": ".$row->deskripsi." Rp. ".toRupiah($row->nilai), 0, 1);
    }

    // separator
    $pdf->Cell(32, 15, "", 0, 1);

    $pdf->Cell(32, 6, "Tanda Tangan", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 6, "__________________________", 0, 0);

    $pdf->SetFont("Arial", "", 9);
    $pdf->Cell(1);
    $pdf->Cell(82, 5, "Saya menjamin kebenaran dan bertanggung jawab", "TLR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "sepenuhnya atas seluruh informasi yang terdapat dalam", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "kuitansi ini.", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 3, "", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "Bendahara	: WAHYU (_____________________)", "LRB", 1);

    $pdf->Cell(32, 5, "Nama Penerima", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5,": ".$kw->penerima_kuitansi, 0, 1);

    $pdf->Cell(32, 5, "ID", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5, ": ".$kw->id_kuitansi, 0, 1);

    $pdf->Cell(10, 1, "", 0, 1);
    $pdf->Cell(10, 1, '________________________________________________________________________________________________________', 0, 1);

    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(125);
    $pdf->Cell(82, 6, "NO. BUKTI: ".$kw->no_bukti."  Tgl.".toRojbDate($kw->tgl), 0, 1);

    $pdf->Output("I", "KUITANSI-".$no_kuitansi.".pdf");
});
// END SERPO RUTIN

// START SERPO MATERIAL
Flight::route("/unit/serpo/material/pembelian", function() {
    $header = [
        "title" => "Unit Serpo Pembelian Material " . APP_NAME,
        "menu" => ["first" => 3, "second" => 31, "third" => 312, "fourth" => 3121]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSerpo = getApiResponse(WEB_API . "serpo/pembelian/material/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    // load referensi material
    $data["order"] = "kode";
    $responseMaterial = getApiResponse(WEB_API . "referensi/material/search/1/1500/_", $data);

    $dataOutput = [
        "dataSerpo" => $responseSerpo->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];
    renderView($header, $dataOutput, "unit/serpo/pembelian_material.php");
});

Flight::route("/unit/serpo/material/pembelian/kuitansi/@no_kuitansi", function($no_kuitansi) {
    $noKuitansi = strReplaceFirst("-", "/", $no_kuitansi);

    $data = [
        "key" => $noKuitansi,
        "order" => "no_bukti",
        "sort" => "asc",
        "search_by" => "no_kuitansi",
        "from_date" => "",
        "to_date" => "",
        "token" => $_SESSION["token"]
    ];        
    $responseKuitansi = getApiResponse(WEB_API . "serpo/pembelian/material/search_by/1/1", $data);

    $kw = $responseKuitansi->result->data[0];

    $total = 0;
    $untukPembayaran = "";
    foreach ($kw->detail as $id => $row)
    {
        $total += (int) $row->nilai_beli;
        $untukPembayaran .= $row->jenis_material." ".$row->qty." ".$row->satuan_material." Rp. ".toRupiah($row->nilai_beli)." ";
    }

    $pdf = new FPDF();
    $pdf->AddPage("L", "A5");
    $pdf->setAuthor("Aditia Rahman");
    $pdf->SetFont("Arial", "B", 12);
    $pdf->Image("themes/img/gsp-logo-long.jpg", 10, 10, 100);
    $pdf->Cell(120);

    $pdf->Cell(0, 5, "KUITANSI GSP", 0, 1);
    $pdf->Cell(120);
    $pdf->SetFont("Arial", "",10);
    $pdf->Cell(0, 10, "No. ".$noKuitansi, 0, 1);

    $pdf->Cell(10, 3, "", 0, 1);
    $pdf->Cell(32, 6, "Sudah terima dari", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": PT GERBANG SINERGI PRIMA", 0, 1);

    $pdf->Cell(32, 6, "Banyaknya uang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ": Rp. ".toRupiah($total), 0, 1);

    $pdf->Cell(32, 6, "Terbilang", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(85, 6, ":".strtoupper(rpTerbilang($total))." RUPIAH", 0, 1);

    foreach ($kw->detail as $id => $row)
    {
        if ($id == 0)
            $pdf->Cell(32, 6, "Untuk pembayaran", 0, 0);
        else
            $pdf->Cell(32, 6, "", 0, 0);

        $pdf->Cell(5);
        $pdf->Cell(85, 6, ": ".$row->jenis_material." ".$row->qty." ".$row->satuan_material." Rp. ".toRupiah($row->nilai_beli), 0, 1);
    }

    // separator
    $pdf->Cell(32, 15, "", 0, 1);

    $pdf->Cell(32, 6, "Tanda Tangan", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 6, "__________________________", 0, 0);

    $pdf->SetFont("Arial", "", 9);
    $pdf->Cell(1);
    $pdf->Cell(82, 5, "Saya menjamin kebenaran dan bertanggung jawab", "TLR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "sepenuhnya atas seluruh informasi yang terdapat dalam", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "kuitansi ini.", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 3, "", "LR", 1);
    $pdf->Cell(103);
    $pdf->Cell(82, 5, "Bendahara	: WAHYU (_____________________)", "LRB", 1);

    $pdf->Cell(32, 5, "Nama Penjual", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5,": ".$kw->supplier, 0, 1);

    $pdf->Cell(32, 5, "Lokasi Penjual", 0, 0);
    $pdf->Cell(5);
    $pdf->Cell(65, 5, ": ".$kw->lokasi, 0, 1);

    $pdf->Cell(10, 1, "", 0, 1);
    $pdf->Cell(10, 1, '________________________________________________________________________________________________________', 0, 1);

    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(115);
    $pdf->Cell(82, 6, "NO. BUKTI: ".$kw->no_bukti."  Tgl.".toRojbDate($kw->tgl), 0, 1);

    $pdf->Output("I", "KUITANSI-".$no_kuitansi.".pdf");
});

Flight::route("/unit/serpo/material/penggunaan", function() {
    $header = [
        "title" => "Unit Serpo Penggunaan Material " . APP_NAME,
        "menu" => ["first" => 3, "second" => 31, "third" => 312, "fourth" => 3122]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responseSerpo = getApiResponse(WEB_API . "serpo/penggunaan/material/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    // load referensi material
    $data["order"] = "kode";
    $responseMaterial = getApiResponse(WEB_API . "referensi/material/search/1/1500/_", $data);

    // load referensi material regional
    $data["order"] = "kode";
    $responseMRegional = getApiResponse(WEB_API . "referensi/material/regional/search/1/1500/_", $data);

    $dataOutput = [
        "dataSerpo" => $responseSerpo->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "dataMRegional" => $responseMRegional->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];
    renderView($header, $dataOutput, "unit/serpo/penggunaan_material.php");
});
// END SERPO MATERIAL

// START PM PENGGUNAAN MATERIAL
Flight::route("/unit/pm/material", function() {
    $header = [
        "title" => "Unit PM Material " . APP_NAME,
        "menu" => ["first" => 3, "second" => 33, "third" => 332, "fourth" => 0]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responsePm = getApiResponse(WEB_API . "pm/material/search/1/1000/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    if ($from_date == 0 || !isValidDate($from_date))
        $from_date = date("d/m/Y");
    else
        $from_date = substr($from_date, 8, 2) . "/" . substr($from_date, 5, 2) . "/" . substr($from_date, 0, 4);

    if ($to_date == 0 || !isValidDate($to_date))
        $to_date = date("d/m/Y");
    else
        $to_date = substr($to_date, 8, 2) . "/" . substr($to_date, 5, 2) . "/" . substr($to_date, 0, 4);

    $range_date = $from_date . " - " . $to_date;

    // load referensi pop
    $data["order"] = "kode";
    $responsePop = getApiResponse(WEB_API . "referensi/pop/search/1/1500/_", $data);

    // load referensi material
    $data["order"] = "kode";
    $responseMaterial = getApiResponse(WEB_API . "referensi/material/search/1/1500/_", $data);

    // load referensi material regional
    $data["order"] = "kode";
    $responseMRegional = getApiResponse(WEB_API . "referensi/material/regional/search/1/1500/_", $data);

    $dataOutput = [
        "dataPm" => $responsePm->result->data,
        "dataPop" => $responsePop->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "dataMRegional" => $responseMRegional->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];
    renderView($header, $dataOutput, "unit/pm/material.php");
});
// END PM PENGGUNAAN MATERIAL