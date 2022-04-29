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

      if (!empty($data['telephone'])){

        if(!(preg_match("/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/", $data['telephone']) === 0) OR $data['telephone']==NULL){

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

      if (!empty($data['photo'])){

        $req = $db->prepare('UPDATE users SET photo = ? WHERE id = ?;');
        $test = $req->execute(array($data['photo'], $data['id']));

        if ($test){ # message bien modifié

          http_response_code(200); # Ok

          echo json_encode(array(
            "status" => true,
            "description" => array("success photo change"),
            "data" => $test
          ));

        } else {
          http_response_code(502); # bad gateway

          echo json_encode(array(
            "status" => false,
            "description" => array("internal_error -> photo change"),
            "returntosender" => $data
          ));
        }
      }

      if (!empty($data['address'])){

        $req = $db->prepare('UPDATE users SET address = ? WHERE id = ?;');
        $test = $req->execute(array($data['address'], $data['id']));

        if ($test){ # message bien modifié

          http_response_code(200); # Ok

          echo json_encode(array(
            "status" => true,
            "description" => array("success address change"),
            "data" => $test
          ));

        } else {
          http_response_code(502); # bad gateway

          echo json_encode(array(
            "status" => false,
            "description" => array("internal_error -> address change"),
            "returntosender" => $data
          ));
        }
      }

      if (!empty($data['password']) AND !(preg_match("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/", $data['password']) === 0)){


        $data['password']=password_hash($data['password'], PASSWORD_DEFAULT);
        $req = $db->prepare('UPDATE users SET password = ? WHERE id = ?;');
        $test = $req->execute(array($data['password'], $data['id']));

        if ($test){ # message bien modifié

          http_response_code(200); # Ok

          echo json_encode(array(
            "status" => true,
            "description" => array("success password change"),
            "data" => $test
          ));

        } else {
          http_response_code(502); # bad gateway

          echo json_encode(array(
            "status" => false,
            "description" => array("internal_error -> password change"),
            "returntosender" => $data
          ));
        }
      }

      if (!empty($data['name']) AND !empty($data['telephone']) AND !empty($data['photo']) AND !empty($data['address']) AND !empty($data['password'])){

        http_response_code(400); # bad request

        echo json_encode(array(
          "status" => false,
          "description" => array("internal_error -> no data"),
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
