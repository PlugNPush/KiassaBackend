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

    if (!$test) {
      $errors[]='invalid_id';
    }
  }

  if (!empty($errors)){
    http_response_code(400); # bad request

    echo json_encode(array(
      "status" => false,
      "description" => $errors,
      "returntosender"=>$data
    ));

    } else {

      if (!empty($data['name'])){

        if($data['name'])!=NULL){

          http_response_code(200);
        } else{

          http_response_code(400); # bad request

          echo json_encode(array(
            "status" => false,
            "description" => array("bad request -> name can't be NULL"),
            "returntosender" => $data
          ));
        }
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
