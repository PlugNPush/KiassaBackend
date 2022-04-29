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
    $errors[]='missing_plainpassword';
  }
  if (empty($data['name'])){
    $errors[]='missing_name';
  }
  # test si les données sont valides
  if (!empty($data['email'])){
    if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
      $errors[]='invalid_email';
    } else {
      # on vérifie si l'email existe déjà dans la db (unique)
      $mail_fetch = $db->prepare('SELECT * FROM users WHERE email = ?;');
      $mail_fetch->execute(array($data['email']));
      $mail = $mail_fetch->fetch();
      if ($mail) {
        $errors[]='email_already_exists';
      }
    }
  }
  if (!empty($data['plainpassword'])){ # 8 caractères, 1 minuscule, 1 majuscule, 1 chiffre, 1 caractère spécial minimum
    if(preg_match("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/", $data['plainpassword']) === 0){
      $errors[]='password_not_conform';
    }
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

  if (!empty($errors)){
    http_response_code(400); # bad request

    echo json_encode(array(
      "status" => false,
      "description" => $errors,
      "returntosender"=>$data
    ));

  } else {

    $data['password']=password_hash($data['plainpassword'], PASSWORD_DEFAULT);
    $data['date'] = date('Y-m-d H:i:s');
    $req=$db->prepare('INSERT INTO users(email, name, telephone, photo, password, address, date) VALUES(:email, :name, :telephone, :photo, :password, :address, :date);');
    $req->execute(array(
      "email" => $data['email'],
      "name" => $data['name'],
      "telephone" => $data['telephone'],
      "photo" => $data['photo'],
      "password" => $data['password'],
      "address" => $data['address'],
      "date" => $data['date']
    ));

    # connexion et vérification de l'enregistrement
    $req = $db->prepare('SELECT * FROM users WHERE email = ?;');
    $req->execute(array($data['email']));
    $test = $req->fetch();

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
      http_response_code(502); # bad gateway

      echo json_encode(array(
        "status" => false,
        "description" => array("internal_error"),
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
