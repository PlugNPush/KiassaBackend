<?php

require_once '../config/core.php';

if ($method == 'DELETE') {

  // Return alert

  # test si les données sont vides
  $errors=array();
  if (empty($data['token'])){
    $errors[]='missing_token';
  }

  if (!empty($errors)){
    http_response_code(412); # precondition failed

    echo json_encode(array(
      "status" => false,
      "description" => $errors,
      "returntosender"=>$data
    ));

    exit();
  }

  # supprimer token de la db
  $req = $db->prepare('DELETE FROM tokens WHERE token = ?;');
  $req->execute(array($data['token']));


  if (!$req){ # mauvais token

    http_response_code(404); # not found

    echo json_encode(array(
      "status" => false,
      "description" => array("token_not_exists"),
      "returntosender" => $data
    ));

  } else { # token bien supprimé

    http_response_code(200); # Ok

    echo json_encode(array(
      "status" => true,
      "description" => array("success")
    ));

  }
} else {

  // Unknown method

  http_response_code(400);

  echo json_encode(array(
    "status" => false,
    "description" => array("unknown_method"),
    "returnmethod" => $method,
    "returntosender" => $data
  ));

}
?>
