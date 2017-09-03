<?php

switch (true) 
{
    /* ================ KANSI WIP DAN HPP ================ */
    case strpos($request->url, "/kansi/wip_hpp/aktivasi") > -1:
        $request->header = [
            "title" => "WIP & HPP Aktivasi " . APP_NAME,
            "menu" => ["first" => 1, "second" => 12, "third" => 122, "fourth" => 0]
        ];
        break;

    /* ================ KANSI REKAPITULASI ================ */
    case strpos($request->url, "/kansi/rekapitulasi/biaya_material") > -1:
        $request->header = [
            "title" => "Rekapitulasi Biaya Material " . APP_NAME,
            "menu" => ["first" => 1, "second" => 14, "third" => 145, "fourth" => 0]
        ];
        break;

    /* ================ KANSI REFERENSI ================ */
    case strpos($request->url, "/kansi/referensi/jenis_jasa") > -1:
        $request->header = [
            "title" => "Referensi Jasa " . APP_NAME,
            "menu" => ["first" => 1, "second" => 15, "third" => 151, "fourth" => 0]
        ];
        break;

    case strpos($request->url, "/kansi/referensi/material") > -1:
        $request->header = [
            "title" => "Referensi Material " . APP_NAME,
            "menu" => ["first" => 1, "second" => 15, "third" => 152, "fourth" => 0]
        ];
        break;

    /* ================ UNIT AKTIVASI ================ */
    case strpos($request->url, "/unit/aktivasi/so/pkt/material/detail") > -1:
        $request->header = [
            "title" => "Detail Material SO " . APP_NAME,
            "menu" => ["first" => 3, "second" => 32, "third" => 322, "fourth" => 3226]
        ];
        break;
}