<?php

require_once '../config/core.php';

if ($method == 'PATCH') {
  # Modifier les données

  if ($connected['status'] == true){

    # test si l'id est valide
    $errors=array();

    if (empty($data['name'])){
      $data['name']=$connected['data']['name'];
    }

    if (empty($data['telephone'])){
      $data['telephone']=$connected['data']['telephone'];
    } else {
      if(!(preg_match("/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/", $data['telephone']) === 0)){
        $errors[]='invalid_telephone';
      }
    }

    if (empty($data['photo'])){
      $data['photo']=$connected['data']['photo'];
    } else {
      if(!filter_var($data['photo'], FILTER_VALIDATE_URL)){
        $errors[]='invalid_photo_url';
      }
    }

    if (empty($data['address'])){
      $data['address']=$object['address'];
    }


    if (!empty($errors)){
      http_response_code(400); # bad request

      echo json_encode(array(
        "status" => false,
        "description" => $errors,
        "returntosender"=>$data
      ));

      } else {

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

        if (!isset($data['name']) AND !isset($data['telephone']) AND !isset($data['photo']) AND !isset($data['address']) AND !isset($data['password'])){

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
            "status" =>true,
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
