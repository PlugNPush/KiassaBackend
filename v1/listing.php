<?php

require_once '../config/core.php';

if ($method == 'POST') {

  // Return alert

  # test si les données sont vides
  $errors=array();
  if (empty($data['name'])){
    $errors[]='missing_name';
  }
  if (empty($data['address'])){ # address du vendeur par défault
    $data['address']='';
  }
  if (empty($data['seller'])){ # id du vendeur
    $errors[]='missing_name';
  }
  # test si les données sont valides
  // Try to convert the string to a float
  $floatVal = floatval($data['price']);
  // If the parsing not succeeded or the value is equivalent to an int
  if(!$floatVal || intval($floatVal) == $floatVal)
  {
      $errors[]='invalid_price';
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
      http_response_code(500); # internal server error

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