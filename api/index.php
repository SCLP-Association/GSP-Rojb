<?php
require "flight/Flight.php";
require "include/common.php";
require "include/pdo.function.php";

// get user request initialization
$request = Flight::request();
$params = json_decode($request->getBody());

// strip controller function
$endSlashPos = strpos(substr($request->url, 1, strlen($request->url)), "/");

// load pdo classes
$pdo = new MyPdo();

// initialize global variable
$g = new stdClass();
$g->pdo = $pdo;
$g->url = $request->url;
$g->control = strtolower(substr($request->url, 1, $endSlashPos));
$g->response = [
    "success" => false,
    "message" => "Undefined method implementation"
];
$g->params = $params;
$g->logged = null;

// load before any route loaded
Flight::before("start", function() use ($g, $pdo, $params) {
    // do authentification
    if ($g->control != "auth") 
    {        
        $sql = "SELECT id, username, full_name, role, regional, token FROM referensi_user 
            WHERE (NOW() - INTERVAL 5 HOUR) < last_login AND token = :token";

        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":token", $params->token, PDO::PARAM_STR);
        $pdo->execute();

        $g->logged = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$g->logged)
        {
            setResponseStatus(false, "Token Expired");
            setExitResponse();
            Flight::stop();
        }        
    }
});

require "controller/" . $g->control . ".php";

Flight::after("start", function() use ($g) {
    // write logs and audit trails
    setExitResponse();
});

Flight::start();
?>
