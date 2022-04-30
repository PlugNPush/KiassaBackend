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
      }

      if (!empty($data['telephone'])){

        if(!(preg_match("/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/", $data['telephone']) === 0){

          $req = $db->prepare('UPDATE users SET telephone = ? WHERE id = ?;');
          $test = $req->execute(array($data['telephone'], $data['id']));

          if ($test){ # message bien modifié

            http_response_code(200); # Ok

            echo json_encode(array(
              "status" => true,
              "description" => array("success telephone change"),
              "data" => $test
            ));

          } else {
            http_response_code(502); # bad gateway

            echo json_encode(array(
              "status" => false,
              "description" => array("internal_error -> telephone change"),
              "returntosender" => $data
            ));
          }
        }  else{

          http_response_code(400); # bad request

          echo json_encode(array(
            "status" => false,
            "description" => array("bad request -> invalid phone number"),
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
