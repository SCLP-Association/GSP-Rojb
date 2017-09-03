<?php
Flight::route("/dashboard/home", function() use ($request) {
    $header = [
        "title" => "Dashboard Home" . APP_NAME,
        "menu" => [
            "first" => 0,
            "second" => 0,
            "third" => 0,
            "fourth" => 0
        ]
    ];

    renderView($header, 
        ["name" => "Aditia Rahman"], 
        "dashboard/home.php");
});