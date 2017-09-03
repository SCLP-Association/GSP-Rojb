<?php
require "flight/Flight.php";
require "includes/fpdf/fpdf.php";
require "includes/web.function.php";
require "includes/var.setting.php";

$request = Flight::request();

// strip controller function
$endSlashPos = strpos(substr($request->url, 1, strlen($request->url)), "/");
$control = strtolower(substr($request->url, 1, $endSlashPos));

// setting header
require "includes/web.header.php";

if ($control != "") require "controller/" . $control . ".php";

Flight::before("start", function() use ($control) {
    // check session token authentification
    if ($control != "auth")
    {
        if (!isset($_SESSION["token"]))
            Flight::redirect("auth/signin");
        else
        {
            // check token to api
            $data = ["token" => $_SESSION["token"]];
            $response = getApiResponse(WEB_API . "user/token_check", $data);
            if (!$response->success)
                Flight::redirect("auth/signin");
        }
    }
});

Flight::start();
?>
