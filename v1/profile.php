<?php

require_once '../config/core.php';

if ($method == 'PUT') {
  # Modifier les données

  if ($connected['status'] == true){

    # test si l'id est valide
    $errors=array();
    if (empty($connected['data']['id'])){
      $errors[]='missing_id';
    } else {
      $req = $db->prepare('SELECT * FROM users WHERE id = ?;');
      $req->execute(array($connected['data']['id']));
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
            $test = $req->execute(array($data['name'], $connected['data']['id']));

            if ($test){ # message bien modifié

              http_response_code(200); # Ok

              echo json_encode(array(
                "status" => true,
                "description" => array("success"),
                "add_on" => ("name change"),
                "data" => $test
              ));

            } else {
              http_response_code(502); # bad gateway

              echo json_encode(array(
                "status" => false,
                "description" => array("internal_error"),
                "add_on" => ("name change"),
                "returntosender" => $data
              ));
            }
        }

        if (isset($data['telephone'])){

          if(!(preg_match("/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/", $data['telephone']) === 0) OR empty($data['telephone'])){

            $req = $db->prepare('UPDATE users SET telephone = ? WHERE id = ?;');
            $test = $req->execute(array($data['telephone'], $connected['data']['id']));

            if ($test){ # message bien modifié

              http_response_code(200); # Ok

              echo json_encode(array(
                "status" => true,
                "description" => array("success"),
                "add_on" => ("telephone change"),
                "data" => $test
              ));

            } else {
              http_response_code(502); # bad gateway

              echo json_encode(array(
                "status" => false,
                "description" => array("internal_error"),
                "add_on" => ("telephone change"),
                "returntosender" => $data
              ));
            }
          }  else{

            http_response_code(400); # bad request

            echo json_encode(array(
              "status" => false,
              "description" => array("bad request"),
              "add_on" => ("invalid phone number"),
              "returntosender" => $data
            ));
          }
        }

        if(isset($data['photo'])){

          $req = $db->prepare('UPDATE users SET photo = ? WHERE id = ?;');
          $test = $req->execute(array($data['photo'], $connected['data']['id']));

          if ($test){ # photo bien modifié

            http_response_code(200); # Ok

            echo json_encode(array(
              "status" => true,
              "description" => array("success"),
              "add_on" => ("photo change"),
              "data" => $test
            ));

          } else {
            http_response_code(502); # bad gateway

            echo json_encode(array(
              "status" => false,
              "description" => array("internal_error"),
              "add_on" => ("photo change"),
              "returntosender" => $data
            ));
          }
        }


        if (isset($data['address'])){

          $req = $db->prepare('UPDATE users SET address = ? WHERE id = ?;');
          $test = $req->execute(array($data['address'], $connected['data']['id']));

          if ($test){ # message bien modifié

            http_response_code(200); # Ok

            echo json_encode(array(
              "status" => true,
              "description" => array("success"),
              "add_on" => ("address change"),
              "data" => $test
            ));

          } else {
            http_response_code(502); # bad gateway

            echo json_encode(array(
              "status" => false,
              "description" => array("internal_error"),
              "add_on" => ("address change"),
              "returntosender" => $data
            ));
          }
        }

        if (isset($data['password']) AND isset($data['plainpassword'])){

          if($data['plainpassword'] == $connected['data']['password']){

            if(!(preg_match("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/", $data['password']) === 0)){

              $data['password']=password_hash($data['password'], PASSWORD_DEFAULT);
              $req = $db->prepare('UPDATE users SET password = ? WHERE id = ?;');
              $test = $req->execute(array($data['password'], $connected['data']['id']));

              if ($test){ # message bien modifié

                http_response_code(200); # Ok

                echo json_encode(array(
                  "status" => true,
                  "description" => array("success"),
                  "add_on" => ("password change"),
                  "data" => $test
                ));

              } else {
                http_response_code(502); # bad gateway

                echo json_encode(array(
                  "status" => false,
                  "description" => array("internal_error"),
                  "add_on" => ("password change"),
                  "returntosender" => $data
                ));
            }

          } else {

            http_response_code(400); # bad request

            echo json_encode(array(
              "status" => false,
              "description" => array("bad request"),
              "add_on" => ("invalid password"),
              "returntosender" => $data
            ));

          }

        } else {

          http_response_code(400); # bad request

          echo json_encode(array(
            "status" => false,
            "description" => array("bad request"),
            "add_on" => ("not similars passwords"),
            "returntosender" => $data
          ));

        }

      } else if(isset($data['password']) OR isset($data['plainpassword'])){

        http_response_code(400); # bad request

        echo json_encode(array(
          "status" => false,
          "description" => array("bad request"),
          "add_on" => ("not selected passwords"),
          "returntosender" => $data
        ));

      }

        if (empty($data['name']) AND empty($data['telephone']) AND empty($data['photo']) AND empty($data['address']) AND empty($data['password'])){

          http_response_code(400); # bad request

          echo json_encode(array(
            "status" => false,
            "description" => array("bad request"),
            "add_on" => ("no data"),
            "returntosender" => $data
          ));
        }
    }

  } else {

    http_response_code(403); # forbiden

    echo json_encode(array(
      "status" => false,
      "description" => array("invalid_token"),
      "returntosender" => $data,
      "returnheaders" => $headers
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
