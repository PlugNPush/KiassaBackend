<?php

require_once '../config/core.php';

if ($method == 'POST'){
    # Creer un profil
    if ($connected['status'] == true) {
      # test si les données sont vides
      $errors=array();
      if (empty($data['name'])){
        $errors[]='missing_name';
      } else {
        $req = $db->prepare('SELECT * FROM users WHERE name = ?;');
        $req->execute(array($data['name']));
        $test = $req->fetch();

        if (!$test) {
          $errors[]='invalid_name';
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

        if (empty($data['photo'])){
          $data['photo']='https://lien_tampon.fr';
        }

        $req = $db->prepare('INSERT INTO users(photo, description) VALUES(:photo, :description);');
        $test = $req->execute(array(
          "photo" => $data['photo'],
        ));

        if ($test==true) {
          http_response_code(201); # created

          echo json_encode(array(
            "status" => true,
            "description" => array("success"),
            "data" => $data
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
      http_response_code(403); # forbiden

      echo json_encode(array(
        "status" => false,
        "description" => array("invalid_token"),
        "returntosender" => $data,
        "returnheaders" => $headers
      ));
    }
  } else if ($method == 'PUT') {
  # Modifier un message

  if ($connected['status'] == true) {
    $errors=array();
    if (empty($data['message'])) {
      $errors[]='misssing_message';
    }

    if (empty($data['newmessage'])) {
      $errors[]='missing_newmessage';
    }

    if (!empty($data['message'])) {
      $req = $db->prepare('SELECT * FROM messages WHERE id = ?;');
      $req->execute(array($data['message']));
      $test = $req->fetch();

      if (!$test) {
        $errors[]="invalid_message";
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
        $req = $db->prepare('UPDATE messages SET message = ?, edited = 1 WHERE id = ?;');
        $test = $req->execute(array($data['newmessage'], $data['message']));

        $req2 = $db->prepare('SELECT * FROM messages WHERE id = ?;');
        $req2->execute(array($data['message']));
        $test2 = $req2->fetch();


        if ($test && $test2){ # message bien modifié

          http_response_code(200); # Ok

          echo json_encode(array(
            "status" => true,
            "description" => array("success"),
            "data" => $test2
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
