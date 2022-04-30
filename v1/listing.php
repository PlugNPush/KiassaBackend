<?php

# status : 0=>privé, 1=>en vente, 2=>en transaction, 3=>vendu

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
    if(!$floatVal || $floatVal<0) // If the parsing not succeeded
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
    if (empty($data['listing'])){
      $errors[]='missing_listing';
    } else { # test si les données sont valides
      # on vérifie si l'objet existe dans la db (id unique)
      $id_fetch = $db->prepare('SELECT * FROM listing WHERE id = ?;');
      $id_fetch->execute(array($data['listing']));
      $object = $id_fetch->fetch();
      if (!$object) {
        $errors[]='invalid_listing';
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

} elseif ($method == 'DELETE') {

  if ($connected['status'] == true){

    // Return alert

    # test si les données sont vides
    $errors=array();
    if (empty($data['listing'])){
      $errors[]='missing_listing';
    } else { # test si les données sont valides
      # on vérifie si l'objet existe dans la db (id unique)
      $id_fetch = $db->prepare('SELECT * FROM listing WHERE id = ?;');
      $id_fetch->execute(array($data['listing']));
      $object = $id_fetch->fetch();
      if (!$object || $object['seller']!=$connected['data']['id']) { # si l'objet n'existe pas ou si l'objet n'appartient pas à l'utilisateur
        $errors[]='invalid_listing';
      }
    }
    if ($data['status'] != 0 && $data['status'] != 1 && $data['status'] != 3) { # 3 automatique
      $errors[]='invalid_status';
    }

    if (!empty($errors)){
      http_response_code(400); # bad request

      echo json_encode(array(
        "status" => false,
        "description" => $errors,
        "returntosender" => $data
      ));

    } else {

      $req = $db->prepare('DELETE FROM listing WHERE id = ?;');
      $test = $req->execute(array($data['listing']));

      if ($test){
        # $object
        http_response_code(200); # ok

        echo json_encode(array(
          "status" => true,
          "description" => array("success")
        ));
      } else {
        http_response_code(502); # bad gateway

        echo json_encode(array(
          "status" => false,
          "description" => array("internal_error"),
          "returntosender" => array("data"=>$data,"listing"=>$object)
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

} elseif ($method == 'PUT') {
  if ($connected['status'] == true){

    // Return alert

    # test si les données sont vides
    $errors=array();
    if (empty($data['listing'])){
      $errors[]='missing_id_object';
    } else {
      $req = $db->prepare('SELECT * FROM listing WHERE id = ?;');
      $req->execute(array($data['listing']));
      $object = $req->fetch(); # on récupére l'objet
      if (!$test) {
        $errors[]='invalid_id_object';
      }
    }
    if ($data['status'] != 0 && $data['status'] != 1) { #$data['status'] = 1 si en vente ou 0 si privé (bouton)
      $errors[]='invalid_status';
    }

    if (!empty($errors)){
      http_response_code(400); # bad request

      echo json_encode(array(
        "status" => false,
        "description" => $errors,
        "returntosender" => $data
      ));

    } else {

      $success=array();

      if (empty($data['name'])){
        $errors[]='missing_name';
        $data['name']=$object['name'];
      } elseif ($data['name']!=$object['name']) {
        $success[]='name_change';
      }
      if (empty($data['address'])){
        $errors[]='missing_address';
        $data['address']=$object['address'];
      } elseif ($data['address']!=$object['address']) {
        $success[]='address_change';
      }
      # test si les données sont valides
      $floatVal = floatval($data['price']); // Try to convert the string to a float
      if(!$floatVal || $floatVal<0)
      {
          $errors[]='invalid_price';
          $data['price']=$object['price'];
      } elseif ($data['price']!=$object['price']) {
        $success[]='price_change';
      }

      if (!empty($data['photo'])){
        if(!filter_var($data['photo'], FILTER_VALIDATE_URL)){
          $errors[]='invalid_photo_url';
          if (!empty($object['photo']){
            $data['photo']=$object['photo'];
          }
        }
      }
      if ($data['photo']!=$object['photo']) {
        $success[]='photo_change';
      }

      if ($data['status']!=$object['status']) {
        $success[]='status_change';
      }

      if (!empty($data['category'])){
        $req = $db->prepare('SELECT * FROM category WHERE id = ?;');
        $req->execute(array($data['category']));
        $test = $req->fetch();
        if (!$test){ # fake category
          $errors[]='invalid_category';
          if (!empty($object['category']){
            $data['category']=$object['category'];
          }
        }
      }
      if ($data['category']!=$object['category']) {
        $success[]='category_change';
      }


      $req=$db->prepare('UPDATE listing SET name=?, address=?, price=?, description=?, status=?, photo=?, category=? WHERE id = ?;');
      $test=$req->execute(array(
        "name" => $data['name'],
        "address" => $data['address'],
        "price" => $data['price'],
        "description" => $data['description'], # non obligatoire
        "status" => $data['status'], # 0 ou 1
        "photo" => $data['photo'], # non obligatoire
        "category" => $data['category'], # non obligatoire
        "id" => $data['listing']
      ));

      if ($test==true && $req->rowCount()==1)
      {
        http_response_code(200); # ok

        echo json_encode(array(
          "status" => true,
          "description" => array("success"=>$success,"errors"=>$errors),
          "data" => $data
        ));
      } else {
        http_response_code(502); # bad gateway

        echo json_encode(array(
          "status" => false,
          "description" => array("internal_error"),
          "returntosender" => $data,
          "update" => array("success"=>$success,"errors"=>$errors)
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
