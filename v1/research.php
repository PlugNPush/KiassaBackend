<?php

require_once '../config/core.php';

if ($method == 'POST') {

  // Return alert

  # test si les données sont vides
  $errors=array();
  if (empty($data['listing'])){
    $errors[]='missing_listing';
  }

  if (!empty($errors)){
    http_response_code(400); # bad request

    echo json_encode(array(
      "status" => false,
      "description" => $errors,
      "returntosender"=>$data
    ));

    exit();
  }

  # recherche de l'email dans la db
  $req = $db->prepare('SELECT * FROM listing WHERE name = ?;');
  $req->execute(array($data['listing']));
  $test = $req->fetch();


  if (!$test){ # mauvais nom de produit

    http_response_code(404); # not found

    echo json_encode(array(
      "status" => false,
      "description" => array("listing_not_exists"),
      "returntosender" => $data
    ));

  } else { # bon nom de produit

    # ajout du token dans la db
    $req2=$db->prepare('SELECT id FROM listing WHERE name = ?;');
    $req2->execute(array(
      "id" => $test['id'],
      "name" => $test['name']
    ));

    if ($req2){
      http_response_code(200); # ok

      echo json_encode(array(
        "status" => true,
        "description" => array("success"),
        "userdata" => $test
      ));
    } else {
      http_response_code(500); # internal server error

      echo json_encode(array(
        "status" => false,
        "description" => array("internal_error"),
        "returntosender" => $data
      ));
    }
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
