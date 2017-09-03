<?php

Flight::route("/stock/pembelian/material/pusat", function() {
    $header = [
        "title" => "Stock Pembelian Material Pusat " . APP_NAME,
        "menu" => ["first" => 2, "second" => 24, "third" => 0, "fourth" => 0]
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
    $responseStock = getApiResponse(WEB_API . "stock/pembelian/material/search/1/1000/" . $key, $data);

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
    $responseMaterial = getApiResponse(WEB_API . "referensi/material/search/1/1500/_", $data);

    $dataOutput = [
        "dataStock" => $responseStock->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];

    renderView($header, $dataOutput, "stock/pembelian_material_pusat.php");
});

Flight::route("/stock/status/material", function() {
    $header = [
        "title" => "Stock Status Material " . APP_NAME,
        "menu" => ["first" => 2, "second" => 21, "third" => 0, "fourth" => 0]
    ];

    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "" : $_GET["key"];
    $params = [
        "kode_material" => $key,
        "token" => $_SESSION["token"]
    ];

    $response = getApiResponse(WEB_API . "stock/status/material", $params);

    $dataOutput = [
        "dataStock" => $response->result->data,
        "key" => $key,
    ];
    
    renderView($header, $dataOutput, "stock/status_material.php");
});

Flight::route("/stock/ih/transfer", function() {
     $header = [
        "title" => "Stock IH Transfer " . APP_NAME,
        "menu" => ["first" => 2, "second" => 22, "third" => 0, "fourth" => 0]
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
    $responseStock = getApiResponse(WEB_API . "stock/ih/transfer/search/1/1000/" . $key, $data);

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
        "dataStock" => $responseStock->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "dataMRegional" => $responseMRegional->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key,
        "user" => $_SESSION["username"]
    ];

    renderView($header, $dataOutput, "stock/ih_transfer.php");
});

Flight::route("/stock/pengeluaran/material", function() {
    $header = [
        "title" => "Stock Pengeluaran Material " . APP_NAME,
        "menu" => ["first" => 2, "second" => 25, "third" => 0, "fourth" => 0]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $data = [
        "order" => "created_at",
        "sort" => "asc",
        "from_date" => $from_date,
        "to_date" => $to_date,
        "tgl_view" => date("d/m/Y"),
        "tgl" => date("Y-m-d"),
        "token" => $_SESSION["token"]
    ];
    $key = (!isset($_GET["key"]) || $_GET["key"] == "") ? "_" : $_GET["key"];
    $responsePengeluaran = getApiResponse(WEB_API . "stock/pengeluaran/material/search/1/1000/" . $key, $data);

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
        "dataPengeluaran" => $responsePengeluaran->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];

    renderView($header, $dataOutput, "stock/pengeluaran_material.php");
});

Flight::route("/stock/pengeluaran/material/request", function() {
    $header = [
        "title" => "Stock Request Material " . APP_NAME,
        "menu" => ["first" => 2, "second" => 25, "third" => 0, "fourth" => 0]
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

    // load referensi material
    $responseMaterial = getApiResponse(WEB_API . "referensi/material/search/1/1500/_", $data);

    $dataOutput = [
        "dataSo" => $responseSo->result->data,
        "dataMaterial" => $responseMaterial->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "tgl" => date("Y-m-d"),
        "tgl_view" => date("d/m/Y"), 
        "key" => $key
    ];

    renderView($header, $dataOutput, "stock/pengeluaran_material_request.php");
});

Flight::route("/stock/saldo/tercatat", function() {
    $header = [
        "title" => "Stock Saldo Tercatat " . APP_NAME,
        "menu" => ["first" => 2, "second" => 27, "third" => 0, "fourth" => 0]
    ];

     $param = [
        "token" => $_SESSION["token"]
    ];
    $responseSaldo = getApiResponse(WEB_API . "stock/saldo/tercatat", $param);
    $dataOutput = [
        "dataSaldo" => $responseSaldo->result->data
    ];

    renderView($header, $dataOutput, "stock/saldo_tercatat.php");
});

Flight::route("/stock/kertas/kerja/@kode", function($kode) {
    $header = [
        "title" => "Stock Kertas Kerja " . $kode . " " . APP_NAME,
        "menu" => ["first" => 2, "second" => 27, "third" => 0, "fourth" => 0]
    ];

    $from_date = (!isset($_GET["from_date"]) || $_GET["from_date"] == "") ? "0" : $_GET["from_date"];
    $to_date = (!isset($_GET["to_date"]) || $_GET["to_date"] == "") ? "0" : $_GET["to_date"];

    $param = [
        "from_date" => $from_date,
        "to_date" => $to_date,
        "token" => $_SESSION["token"]
    ];
    $responseKertasKerja = getApiResponse(WEB_API . "stock/kertas/kerja/" . $kode, $param);
    
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
        "dataKertasKerja" => $responseKertasKerja->result->data,
        "range_date" => $range_date, 
        "from_date" => $from_date, 
        "to_date" => $to_date,
        "kode" => $kode
    ];
    renderView($header, $dataOutput, "stock/kertas_kerja.php");
});