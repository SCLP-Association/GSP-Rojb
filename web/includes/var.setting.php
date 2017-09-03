<?php
$lifetime = 10800;
session_set_cookie_params($lifetime);
ini_set("session.gc_maxlifetime", $lifetime);
session_start();

define("BASE_URL", "http://localhost:8080/siakangrosi/");
define("WEB_URL", BASE_URL . "web/");
define("WEB_THEME", WEB_URL . "themes/");
define("WEB_API", BASE_URL . "api/");
define("APP_NAME", " | SiAkangRosi GSP");
define("JS_VER", uniqid());