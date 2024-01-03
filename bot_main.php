<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__.'/php_errors.log');

require __DIR__ . "/rb-mysql.php";
require __DIR__ . "/template.php";
require __DIR__ . "/Bot_V2.php";
require 'vendor/autoload.php';

$config = parse_ini_file(__DIR__ . "/configs.ini", true);

$bot = new Bot_V2();
header("HTTP/1.0 200 OK");
$bot->init();