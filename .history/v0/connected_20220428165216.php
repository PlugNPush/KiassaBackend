<?php

require_once '../config/core.php';

if ($method == 'POST') {

  // Return alert
    $connected = connected();

    if ($connected['status'] == true){
      http_response_code(200); # ok

      echo json_encode(array(
        "status" => true,
        "description" => array("success"),
        "data" => array("token"=>$token,"expiration"=>$date),
        "userdata" => $test
      ));
    } else {
      http_response_code(500); # internal server error

      echo json_encode(array(
        "status" => false,
        "description" => array("internal_error"),
        "returntosender" => $data
      ));
    }
  } else { # mauvais mdp
    http_response_code(403); # not allowed

    echo json_encode(array(
      "status" => false,
      "description" => array("password_missmatch"),
      "returntosender" => $data
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
