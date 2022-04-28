<?php
require_once 'config/core.php';

if ($method == 'GET') {

  // Return alert

  $text = "Le piratage est puni par la loi.";

  http_response_code(403);

  echo json_encode(array(
    "status" => false,
    "description" => $text
  ));

} else if ($method == 'POST') {

  // Return alert

  $text = "Vous avez tenté de procéder à des modifications non autorisées. Le piratage est puni par la loi.";

  http_response_code(403);

  echo json_encode(array(
    "status" => false,
    "description" => $text,
    "returntosender" => $data
  ));


} else {

  // Unknown method


  http_response_code(405); # method not allowed
  echo json_encode(array(
    "status" => false,
    "returntosender" => $data
  ));

}
