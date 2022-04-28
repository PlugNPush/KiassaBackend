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
  $test = $req->fetchAll();


  if (!$test){ # pas de résultats

    http_response_code(203); # no content

    echo json_encode(array(
      "status" => false,
      "description" => array("no_results")
    ));

  } else { # résultats ok

      http_response_code(200); # ok

      echo json_encode(array(
        "status" => true,
        "description" => array("success"),
        "userdata" => $test
      ));
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
