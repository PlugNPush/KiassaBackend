<?php

require_once '../config/core.php';

if ($method == 'PUT') {
  # Modifier les données

  # test si le nom est valide
  $errors=array();
  if (empty($data['id'])){
    $errors[]='missing_id';
  } else {
    $req = $db->prepare('SELECT * FROM users WHERE id = ?;');
    $req->execute(array($data['id']));
    $test = $req->fetch();
  }



} else {

  // Unknown method

  http_response_code(405); # method not allowed

  echo json_encode(array(
    "status" => false,
    "description" => array("unknown_method"),
    "returnmethod" => $method,
    "returntosender" => $data
  ));

}
?>
