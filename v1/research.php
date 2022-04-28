<?php

require_once '../config/core.php';

if ($method == 'POST') {

  // Return alert

  # test si les données sont vides
  $errors=array();
  if (empty($data['search'])){
    $errors[]='missing_search';
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
  $req = $db->prepare('SELECT * FROM listing WHERE ((name LIKE ? OR description LIKE ?) AND  status = 1);');
  $req->execute(array(
    '%'.$data['search'].'%',
    '%'.$data['search'].'%'
  ));
  $test = $req->fetch();


  if (!$test){ # pas de résultats

    http_response_code(204); # no content

    echo json_encode(array(
      "status" => false,
      "description" => array("no_results")
    ));

  } else { # résultats ok

    # ajout du token dans la db
    $req2=$db->prepare('SELECT id, name, price, description, status, photo, seller, category FROM listing WHERE name = ?;');
    $req2->execute(array(
      "id" => $test['id'],
      "name" => $test['name'],
      "price" => $test['price'],
      "description" => $test['description'],
      "photo" => $test['photo'],
      "seller" => $test['seller'],
      "category" => $test['category'],
    ));

    if ($req2){
      http_response_code(200); # ok

      echo json_encode(array(
        "status" => true,
        "description" => array("success"),
        "userdata" => $test
      ));
    } else {
      http_response_code(502); # bad gateway

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
