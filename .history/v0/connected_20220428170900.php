<?php

require_once '../config/core.php';

if ($method == 'GET') {

  // Return alert
    $connected = connected();

    if ($connected['status'] == true){
      http_response_code(200); # ok

      echo json_encode(array(
        $connected
      ));
    } else {
      http_response_code(400); # bad request

      echo json_encode(array(
        $connected
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
