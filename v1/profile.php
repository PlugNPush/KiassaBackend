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

          $req = $db->prepare('UPDATE users SET name = ? WHERE id = ?;');
          $test = $req->execute(array($data['name'], $data['id']));

          if ($test){ # message bien modifié

            http_response_code(200); # Ok

            echo json_encode(array(
              "status" => true,
              "description" => array("success name change"),
              "data" => $test
            ));

          } else {
            http_response_code(502); # bad gateway

            echo json_encode(array(
              "status" => false,
              "description" => array("internal_error -> name change"),
              "returntosender" => $data
            ));
          }
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
