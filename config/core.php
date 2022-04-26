<?php
// Main config file
require_once dirname(__FILE__).'/../../../config/config.php';

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Token, username, password");

// Read data from request
$method = $_SERVER["REQUEST_METHOD"];
if($method == 'OPTIONS'){
  exit;
}else if($method == 'POST' || $method == 'PUT'){
  $data = json_decode(file_get_contents('php://input'), true);
}

// Connect to database
try {
  $db = new PDO('mysql:host='.getDBHost().';dbname=kiassa', getDBUsername(), getDBPassword(), array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb"));
} catch(Exception $e) {
	exit ('Erreur while connecting to database: '.$e->getMessage());
}
