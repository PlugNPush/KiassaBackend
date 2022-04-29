<?php

require_once '../config/core.php';

if ($method == 'POST') {
  if ($connected['status'] == true){

    // Return alert

    # test si les données sont vides
    $errors=array();
    if (empty($data['name'])){
      $errors[]='missing_name';
    }
    if (empty($data['address'])){
      if (!empty($connected['data']['address'])){
        $data['address']=$connected['data']['address']; # address du vendeur par défaut
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
    if ($data['status'] != 0 && $data['status'] != 1) { #$data['status'] = 1 si en vente ou 0 si privé (bouton)
      $errors[]='invalid_status';
    }
    if (empty($data['status'])){ # par défaut en vente
      $data['status']=1;
    }
    if (!empty($data['category'])){ #$data['category'] = null ou 1 catégorie à séléctionner (liste)
      $req = $db->prepare('SELECT * FROM category WHERE id = ?;');
      $req->execute(array($data['category']));
      $test = $req->fetch();
      if (!$test){ # fake category
        $errors[]='invalid_category';
      }
    }

    if (!empty($errors)){
      http_response_code(400); # bad request

      echo json_encode(array(
        "status" => false,
        "description" => $errors,
        "returntosender" => $data
      ));

    } else {

      $data['seller'] = $connected['data']['id'];
      $data['date'] = date('Y-m-d H:i:s');
      $req=$db->prepare('INSERT INTO listing(name, address, price, description, status, photo, seller, category, date) VALUES(:name, :address, :price, :description, :status, :photo, :seller, :category, :date);');
      $test=$req->execute(array(
        "name" => $data['name'],
        "address" => $data['address'],
        "price" => $data['price'],
        "description" => $data['description'], # non obligatoire
        "status" => $data['status'], # 0 ou 1
        "photo" => $data['photo'], # non obligatoire
        "seller" => $data['seller'],
        "category" => $data['category'], # non obligatoire
        "date" => $data['date']
      ));

      if ($test==true && $req->rowCount()==1)
      {
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

} elseif ($method == 'PATCH') {

  if ($connected['status'] == true){

    // Return alert

    # test si les données sont vides
    $errors=array();
    if (empty($data['id'])){
      $errors[]='missing_id';
    } else { # test si les données sont valides
      # on vérifie si l'objet existe dans la db (id unique)
      $id_fetch = $db->prepare('SELECT * FROM listing WHERE id = ?;');
      $id_fetch->execute(array($data['id']));
      $object = $id_fetch->fetch();
      if (!$object) {
        $errors[]='invalid_id';
      }
    }

    if (!empty($errors)){
      http_response_code(400); # bad request

      echo json_encode(array(
        "status" => false,
        "description" => $errors,
        "returntosender" => $data
      ));

    } else {

      # $object
      http_response_code(200); # ok

      echo json_encode(array(
        "status" => true,
        "description" => array("success"),
        "data" => $object
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
