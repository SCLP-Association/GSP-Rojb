<?php
Flight::route("/kansi/petty_cash", function() use ($request) {
    $header = [
        "title" => "Petty Cash " . APP_NAME,
        "menu" => ["first" => 1, "second" => 11, "third" => 151, "fourth" => 0
        ]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["key" => $key], "kansi/petty_cash.php");
});

/* =========================== KANSI WIP DAN HPP =========================== */
Flight::route("/kansi/wip_hpp/aktivasi", function() use($request) {

    $posisi_tgl = (isset($_GET["posisi_date"])) ? $_GET["posisi_date"] : date("Y-m-d");
    $data = [
        "posisi_tgl" => $posisi_tgl,
        "token" => $_SESSION["token"]
    ];

    $response = getApiResponse(WEB_API . "kansi_wip/aktivasi", $data);

    if ($posisi_tgl == 0 || !isValidDate($posisi_tgl))
        $posisi_tgl = date("d/m/Y");
    else
        $posisi_tgl = substr($posisi_tgl, 8, 2) . "/" . substr($posisi_tgl, 5, 2) . "/" . substr($posisi_tgl, 0, 4);

    $dataOutput = [
        "dataWip" => $response->result->data,
        "posisi_date" => $posisi_tgl
    ];
    renderView($request->header, $dataOutput, "kansi/wip_hpp/wip_aktivasi.php");
});

/* =========================== KANSI REKAPITULASI =========================== */
Flight::route("/kansi/rekapitulasi/biaya_material", function() use($request) {

    $posisi_tgl = (isset($_GET["posisi_date"])) ? $_GET["posisi_date"] : date("Y-m-d");
    $data = [
        "posisi_tgl" => $posisi_tgl,
        "token" => $_SESSION["token"]
    ];

    $response = getApiResponse(WEB_API . "kansi_rekap/biaya_material", $data);

    if ($posisi_tgl == 0 || !isValidDate($posisi_tgl))
        $posisi_tgl = date("d/m/Y");
    else
        $posisi_tgl = substr($posisi_tgl, 8, 2) . "/" . substr($posisi_tgl, 5, 2) . "/" . substr($posisi_tgl, 0, 4);


    $dataOutput = [
        "posisi_date" => $posisi_tgl,
        "rekap" => $response->result->data
    ];

    renderView($request->header, $dataOutput, "kansi/rekapitulasi/biaya_material.php");
});

/* =========================== KANSI REFERENSI =========================== */
Flight::route("/kansi/referensi/jenis_jasa", function() use ($request) {
    $data = [
        "order" => "kode",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/jasa/search/1/150/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    renderView($request->header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/jenis_jasa.php");
});

Flight::route("/kansi/referensi/material", function() use ($request) {
    $data = [
        "order" => "kode",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/material/search/1/150/" . $key, $data);

    $key = ($key == "_") ? "" : $key;
    renderView($request->header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/material.php");
});

Flight::route("/kansi/referensi/material_regional", function() use ($request) {
    $data = [
        "order" => "kode",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/material/regional/search/1/150/" . $key, $data);

    $header = [
        "title" => "Referensi Material Regional " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 1511, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/material_regional.php");
});

Flight::route("/kansi/referensi/regional", function() use ($request) {
    $data = [
        "order" => "kode",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/regional/search/1/150/" . $key, $data);

    $header = [
        "title" => "Referensi Regional " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 153, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/regional.php");
});

Flight::route("/kansi/referensi/jenis_biaya", function() use ($request) {
    $data = [
        "order" => "kode",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/biaya/search/1/150/" . $key, $data);

    $header = [
        "title" => "Referensi Jenis Biaya " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 154, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/jenis_biaya.php");
});

Flight::route("/kansi/referensi/pelanggan", function() use ($request) {
    $data = [
        "order" => "nama",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/pelanggan/search/1/150/" . $key, $data);

    $header = [
        "title" => "Referensi Pelanggan " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 155, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/pelanggan.php");
});

Flight::route("/kansi/referensi/khs", function() use ($request) {
    $data = [
        "order" => "kode",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/khs/search/1/150/" . $key, $data);

    $header = [
        "title" => "Referensi KHS " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 156, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/khs.php");
});

Flight::route("/kansi/referensi/pop", function() use ($request) {
    $data = [
        "order" => "kode",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/pop/search/1/150/" . $key, $data);

    $header = [
        "title" => "Referensi Pelanggan " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 157, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/pop.php");
});

Flight::route("/kansi/referensi/plat", function() use ($request) {
    $data = [
        "order" => "no_plat",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/plat/search/1/150/" . $key, $data);

    $header = [
        "title" => "Nomor Plat Kendaraan " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 158, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/plat.php");
});

Flight::route("/kansi/referensi/etoll", function() use ($request) {
    $data = [
        "order" => "no_kartu",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/etoll/search/1/150/" . $key, $data);

    $header = [
        "title" => "Nomor Kartu E-Toll " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 159, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/etoll.php");
});

Flight::route("/kansi/referensi/hp", function() use ($request) {
    $data = [
        "order" => "no_kartu",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "referensi/hp/search/1/150/" . $key, $data);

    $header = [
        "title" => "Nomor Kartu HP " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 1510, "fourth" => 0]
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, ["data" => $response->result->data, "key" => $key], "kansi/referensi/hp.php");
});

Flight::route("/kansi/referensi/daftar_user", function() use ($request) {
    $data = [
        "order" => "username",
        "sort" => "asc",
        "token" => $_SESSION["token"]
    ];

    $key = (isset($_GET["key"])) ? $_GET["key"] : "_";

    $response = getApiResponse(WEB_API . "user/search/1/150/" . $key, $data);

    $data["order"] = "kode";
    $responseRegional = getApiResponse(WEB_API . "referensi/material/regional/search/1/150/_", $data);

    $header = [
        "title" => "Daftar User " . APP_NAME,
        "menu" => ["first" => 1, "second" => 15, "third" => 151200, "fourth" => 0]
    ];

    $dataOutput = [
        "data" => $response->result->data, 
        "dataRegional" => $responseRegional->result->data,
        "key" => $key
    ];

    $key = ($key == "_") ? "" : $key;
    renderView($header, $dataOutput, "kansi/referensi/daftar_user.php");
});