<?php

require_once '../config/core.php';

if (!empty($_GET['id'])) {
  $data['receiver'] = $_GET['id'];
}


if ($method == 'GET') {
  if ($connected['status'] == true) {
    # lister les conversations
    $req = $db->prepare('SELECT CASE WHEN receiver = ? THEN sender ELSE receiver END AS contact, max(date) AS date FROM messages WHERE receiver = ? OR sender = ? GROUP BY CASE WHEN receiver = ? THEN sender ELSE receiver END ORDER BY date DESC;');
    $req->execute(array($connected['data']['id'], $connected['data']['id'], $connected['data']['id'], $connected['data']['id']));
    $test = $req->fetchAll();

    if ($test)
    {
      http_response_code(200); # ok

      echo json_encode(array(
        "status" => true,
        "description" => array("success"),
        "data" => $test
      ));
    } else {
      http_response_code(204); # no content

      echo json_encode(array(
        "status" => true,
        "description" => array("no_results")
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

} else if ($method == 'GET') {
  if ($connected['status'] == true) {
    $errors=array();
    if (empty($data['receiver'])){
      $errors[]='missing_receiver';
    } else {
      $req = $db->prepare('SELECT * FROM users WHERE id = ?;');
      $req->execute(array($data['receiver']));
      $test = $req->fetch();

      if (!$test || $data['receiver'] == $connected['data']['id']) {
        $errors[]='invalid_receiver';
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
      # lister les messages pour un destinataire

      $req = $db->prepare('SELECT * FROM messages WHERE (sender = ? AND receiver = ?) OR (receiver = ? AND sender = ?) ORDER BY date DESC;');
      $req->execute(array($connected['data']['id'], $data['receiver'], $connected['data']['id'], $data['receiver']));
      $test = $req->fetchAll();

      if ($test)
      {
        http_response_code(200); # ok

        echo json_encode(array(
          "status" => true,
          "description" => array("success"),
          "data" => $test
        ));
      } else {
        http_response_code(204); # no content

        echo json_encode(array(
          "status" => true,
          "description" => array("no_results")
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
} else if ($method == 'POST'){
    # Envoyer un message
    if ($connected['status'] == true) {
      # test si les données sont vides
      $errors=array();
      if (empty($data['receiver'])){
        $errors[]='missing_receiver';
      } else {
        $req = $db->prepare('SELECT * FROM users WHERE id = ?;');
        $req->execute(array($data['receiver']));
        $test = $req->fetch();

        if (!$test || $data['receiver'] == $connected['data']['id']) {
          $errors[]='invalid_receiver';
        }
      }
      if (empty($data['content'])){
        $errors[]='missing_content';
      }


      if (!empty($errors)){
        http_response_code(400); # bad request

        echo json_encode(array(
          "status" => false,
          "description" => $errors,
          "returntosender"=>$data
        ));

      } else {
        $data['date'] = date('Y-m-d H:i:s');
        $data['sender'] = $connected['data']['id'];

        $req = $db->prepare('INSERT INTO messages(sender, receiver, content, date) VALUES(:sender, :receiver, :content, :date);');
        $test = $req->execute(array(
          "sender" => $data['sender'],
          "receiver" => $data['receiver'],
          "content" => $data['content'],
          "date" => $data['date']
        ));

        if ($test==true && $req->rowCount()==1) {
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
  } else if ($method == 'DELETE') {
  # Supprimer un message ou une conversation entière

  if ($connected['status'] == true) {
    $errors=array();
    if (empty($data['message']) && empty($data['receiver'])) {
      $errors[]='misssing_ambigious_data';
    } else if (!empty($data['message']) && !empty($data['receiver'])) {
      $errors[]='extra_ambigious_data';
    }

    if (!empty($data['message'])) {
      $req = $db->prepare('SELECT * FROM messages WHERE id = ?;');
      $req->execute(array($data['message']));
      $test = $req->fetch();

      if (!$test) {
        $errors[]="invalid_message";
      }
    }

    if (!empty($data['receiver'])) {
      $req = $db->prepare('SELECT * FROM users WHERE id = ?;');
      $req->execute(array($data['receiver']));
      $test = $req->fetch();

      if (!$test || $data['receiver'] == $connected['data']['id']) {
        $errors[]="invalid_receiver";
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
      if (!empty($data['message'])) {
        # Supprimer un message
        $req = $db->prepare('DELETE FROM messages WHERE id = ?;');
        $test = $req->execute(array($data['message']));

        if ($test){ # message bien supprimé

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
        # Supprimer une conversation entiere
        $req = $db->prepare('DELETE FROM messages WHERE (sender = ? AND receiver = ?) OR (receiver = ? AND sender = ?);');
        $test = $req->execute(array($connected['data']['id'], $data['receiver'], $connected['data']['id'], $data['receiver']));

        if ($test){ # conversation bien supprimée

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


} else if ($method == 'PATCH') {
  # Modifier un message

  if ($connected['status'] == true) {
    $errors=array();
    if (empty($data['message'])) {
      $errors[]='misssing_message';
    }

    if (empty($data['newcontent'])) {
      $errors[]='missing_newcontent';
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
        $req = $db->prepare('UPDATE messages SET content = ?, edited = 1 WHERE id = ?;');
        $test = $req->execute(array($data['newcontent'], $data['message']));

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
