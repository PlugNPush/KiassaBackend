<?php

require_once '../config/core.php';

$connected = connected();

if ($method == 'POST') {
  if ($connected['status'] == true){

    // Return alert

    # test si les données sont vides
    $errors=array();
    if (empty($data['name'])){
      $errors[]='missing_name';
    }
    if (empty($data['address'])){
      if (!empty($connnected['data']['address'])){
        $data['address']=$connnected['data']['address']; # address du vendeur par défault
      } else {
        $errors[]='missing_address';
      }
    }
    # test si les données sont valides
    $floatVal = floatval($data['price']); // Try to convert the string to a float
    if(!$floatVal) // If the parsing not succeeded
    {
        $errors[]='invalid_price';
    }
    if (!empty($data['photo'])){
      if(!filter_var($data['photo'], FILTER_VALIDATE_URL)){
        $errors[]='invalid_photo_url';
      }
    }
    #$data['status'] = 1 si en vente ou 0 si privé (bouton)
    $data['seller'] = $connnected['data']['id']
    #$data['category'] = null ou 1 catégorie à séléctionner (liste)

    if (!empty($errors)){
      http_response_code(400); # bad request

      echo json_encode(array(
        "status" => false,
        "description" => $errors,
        "returntosender" => $data
      ));

    } else {

      $data['date'] = date('Y-m-d H:i:s');
      $req=$db->prepare('INSERT INTO listing(name, address, price, description, status, photo, seller, category, date) VALUES(:name, :address, :price, :description, :status, :photo, :seller, :category, :date);');
      $req->execute(array(
        "name" => $data['name'],
        "address" => $data['address'],
        "price" => $data['price'],
        "description" => $data['description'], # non obligatoire
        "status" => $data['status'], # 0 ou 1
        "photo" => $data['photo'], # non obligatoire
        "seller" => $data['seller'],
        "category" => $data['category'],
        "date" => $data['date']
      ));

      if ($req == true)
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
