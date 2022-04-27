<?php

require_once '../config/core.php';

if ($method == 'POST') {

  // Return alert

  # test si les données sont vides
  $errors=array();
  if (empty($data['email'])){
    $errors[]='missing_email';
  }
  if (empty($data['plainpassword'])){
    $errors[]='missing_password';
  }
  # test si les données sont valides
  if (!empty($data['email'])){
    if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
      $errors[]='invalid_email';
    }
  }
  if (!empty($data['plainpassword'])){ # 8 caractères, 1 minuscule, 1 majuscule, 1 chiffre, 1 caractère spécial minimum
    if(preg_match("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/", $data['plainpassword']) === 0){
      $errors[]='password_not_conform';
    }
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

  # recherche de l'email dans la db
  $req = $db->prepare('SELECT * FROM users WHERE email = ?;');
  $req->execute(array($data['email']));
  $test = $req->fetch();

  # check mdp correspondant à l'email avec celui existant
  $verify = password_verify($data['plainpassword'], $test['password']);
  if ($verify)
  {
    http_response_code(201); # created

    echo json_encode(array(
      "status" => true,
      "description" => array("success"),
      "data" => $test
    ));
  } else {
    http_response_code(500); # internal server error

    echo json_encode(array(
      "status" => false,
      "description" => array("internal_error"),
      "returntosender" => $data
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
