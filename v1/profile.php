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

    if (!empty($data['telephone'])){
      if(preg_match("/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/", $data['telephone']) === 0){
        $errors[]='invalid_telephone';
      }
    }

    if (!empty($data['photo'])){
      if(!filter_var($data['photo'], FILTER_VALIDATE_URL)){
        $errors[]='invalid_photo_url';
      }
    }

    if (!isset($data['address'])){
      $data['address']=$connected['data']['address'];
    }

    if (isset($data['password']) AND isset($data['plainpassword'])){
      $verify = password_verify($data['plainpassword'], $connected['data']['password']);
      if($verify){
        if(!(preg_match("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/", $data['password']) === 0)){
          $data['password']=password_hash($data['password'], PASSWORD_DEFAULT); # mdp ok
        } else {
          $errors[]='invalid_password';
        }
      } else {
        $errors[]='invalid_plainpassword';
      }
    } else if(isset($data['password']) OR isset($data['plainpassword'])){
      $errors[]='not_selected_passwords';
    }


    if (!empty($errors)){
      http_response_code(400); # bad request

      echo json_encode(array(
        "status" => false,
        "description" => $errors,
        "returntosender"=>$data
      ));

      } else {

        $req = $db->prepare('UPDATE users SET name=?, telephone=?, photo=?, address=?, password=? WHERE id=?;');
        $test = $req->execute(array($data['name'], $data['telephone'], $data['photo'], $data['address'], $data['password'], $connected['data']['id']));

        if($test==true && $req->rowCount()==1) {
          http_response_code(200); # Ok

          echo json_encode(array(
            "status" => true,
            "description" => array("success"),
            "data" => $test
          ));
        } else {
          http_response_code(502); # bad gateway

          echo json_encode(array(
            "status" => false,
            "description" => array("internal_error"),
            "returntosender" => $data,
            "test" => $connected['data']
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
