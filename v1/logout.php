<?php

require_once '../config/core.php';

if ($method == 'DELETE') {

  if ($connected['status'] == true){

    // Return alert

    # test si les données sont vides
    $errors=array();
    if (empty($connected['tokendata']['token'])){
      $errors[]='missing_token';
    } else {
      $req = $db->prepare('SELECT * FROM tokens WHERE token = ?;');
      $req->execute(array($connected['tokendata']['token']));
      $test = $req->fetch();
      if (!$test){
        $errors[]='token_not_exists';
      }
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

    # supprimer token de la db


    $req2 = $db->prepare('DELETE FROM tokens WHERE token = ?;');
    $req2->execute(array($connected['tokendata']['token']));

    $req3 = $db->prepare('SELECT * FROM tokens WHERE token = ?;');
    $req3->execute(array($connected['tokendata']['token']));
    $test3 = $req3->fetch();


    if (!$test3){ # token bien supprimé

      http_response_code(200); # Ok

      echo json_encode(array(
        "status" => true,
        "description" => array("success")
      ));

    } else {
      http_response_code(502); # bad gateway

      echo json_encode(array(
        "status" => false,
        "description" => array("internal_error"),
        "returntosender" => $data
      ));
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
