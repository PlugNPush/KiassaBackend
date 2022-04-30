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

        $success=array();

        if (!empty($data['name'])){

            $req = $db->prepare('UPDATE users SET name = ? WHERE id = ?;');
            $test = $req->execute(array($data['name'], $connected['data']['id']));

            if ($test){ # name bien modifié

              $success[]='200 - success name change';

            } else {

              $errors[]='502 - internal_error name change';

            }
        }

        if (isset($data['telephone'])){

          if(!(preg_match("/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/", $data['telephone']) === 0) OR empty($data['telephone'])){

            $req = $db->prepare('UPDATE users SET telephone = ? WHERE id = ?;');
            $test = $req->execute(array($data['telephone'], $connected['data']['id']));

            if ($test){ # telephone bien modifié

              $success[]='200 - success phone change';

            } else {

              $errors[]='502 - internal_error phone change';

            }
          }  else{

            $errors[]='400 - bad request invalid phone number';

          }
        }

        if(isset($data['photo'])){

          $req = $db->prepare('UPDATE users SET photo = ? WHERE id = ?;');
          $test = $req->execute(array($data['photo'], $connected['data']['id']));

          if ($test){ # photo bien modifié

            $success[]='200 - success photo change';

          } else {

            $errors[]='502 - internal_error photo change';

          }
        }


        if (isset($data['address'])){

          $req = $db->prepare('UPDATE users SET address = ? WHERE id = ?;');
          $test = $req->execute(array($data['address'], $connected['data']['id']));

          if ($test){ # address bien modifié

            $success[]='200 - success address change';

          } else {

            $errors[]='502 - internal_error address change';

          }
        }

        if (isset($data['password']) AND isset($data['plainpassword'])){

          $verify = password_verify($data['plainpassword'], $connected['data']['password']);

          if($verify){

            if(!(preg_match("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/", $data['password']) === 0)){

              $data['password']=password_hash($data['password'], PASSWORD_DEFAULT);
              $req = $db->prepare('UPDATE users SET password = ? WHERE id = ?;');
              $test = $req->execute(array($data['password'], $connected['data']['id']));
              if ($test){ # password bien modifié

                $success[]='200 - success password change';

              } else {

                $errors[]='502 - internal_error password change';

              }

            } else {

              $errors[]='400 - bad request invalid password';

            }

          } else {

            $errors[]='400 - bad request no good plainpassword';

          }

        } else if(isset($data['password']) OR isset($data['plainpassword'])){

          $errors[]='400 - bad request not selected passwords';

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

        if(!empty($errors)){

          if(empty($success)){
            $success[]='No Success';
          }

          http_response_code(206);

          echo json_encode(array(
            "status" =>false,
            "description" => array("partial_content"),
            "errors" => $errors,
            "success" => $success,
            "returntosender" => $data
          ));

        } else {

          http_response_code(200); # Ok

          echo json_encode(array(
            "status" => true,
            "description" => array("success"),
            "success" => $success,
            "data" => $test
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
