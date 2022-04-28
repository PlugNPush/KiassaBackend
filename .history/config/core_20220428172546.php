<?php
// Main config file
require_once dirname(__FILE__).'/../../../config/config.php';
require_once dirname(__FILE__).'/../../../config/kiassaconfig.php';

require '../3rd_parties/PHPMailer/PHPMailer.php';
require '../3rd_parties/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Token, username, password");
header("Content-Type: application/json; charset=utf-8");

// Read data from request
$method = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents('php://input'), true);
$headers = getallheaders();

// Connect to databases
try {
  $db = new PDO('mysql:host='.getDBHost().';dbname=Kiassa', getDBUsername(), getDBPassword(), array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
} catch(Exception $e) {
	exit ('Erreur while connecting to database: '.$e->getMessage());
}

function generateRandomString($length = 255) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function sendMail($to, $subject, $message){
  /*  $to = $data['email'];
      $subject = 'Verification automatique Kiassa';
      $message = '
          <html><body>
            <h1>Vérification automatique de mail.</h1>
            <p>Bonjour ' . $_SESSION['pseudo'] . ', et bienvenue sur Efrei Dynamo. Pour confirmer votre inscription, vous devez prouver votre appartenance à l\'Efrei. Grâce à votre adresse email Efrei, vous êtes éligible à notre solution de validation automatique. Cliquez simplement sur le lien ci-dessous pour terminer l\'activation de votre compte.
            <p>Adresse email utilisée</p>
            <h4>' . $_POST['email'] . '</h4>
            <p>Certification demandée le</p>
            <h4>' . $date . '</h4>
            <br>
            <h3><a href="https://www.efrei-dynamo.fr/validation.php?token=' . $token . '">Cliquez ici pour activer automatiquement votre compte</a>.</h3>
            <br>
            <p>En cas de problème avec le lien ci-dessus, vous pouvez aussi copier votre code d\'authentification à usage unique :</p>
            <h4>' . $token . '</h4>
            <br>
            <p>À très vite !</p>
            <p>- L\'équipe Efrei Dynamo.</p><br><br>
            <p>P.S.: Ce courriel est automatique, veuillez ne pas y répondre.</p>
         </body></html>
        ';*/


      // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
      $headers[] = 'MIME-Version: 1.0';
      $headers[] = 'Content-type: text/html; charset=iso-8859-1';

      // En-têtes additionnels
      $headers[] = 'To: <' . $data['email'] . '>';
      $headers[] = 'From: Validation Kiassa <no-reply@kiassa.org>';

      $mail = new PHPmailer();
      $mail->IsSMTP();
      $mail->IsHTML(true);
      $mail->CharSet = 'UTF-8';
      $mail->Host = 'smtp.free.fr';
      $mail->Port = 465;
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Username = 'craftsearch';
      $mail->Password = getSMTPPassword();
      $mail->SMTPOptions = array(
          'ssl' => array(
             'verify_peer' => false,
             'verify_peer_name' => false,
             'allow_self_signed' => true
          )
      );
      $mail->From = 'no-reply@kiassa.org';
      $mail->FromName = 'Validation Kiassa';
      $mail->AddAddress($to);
      $mail->Subject = $subject;
      $mail->Body = $message;

      // Send the mail
      $sent = $mail->send();
      // Envoi
      //$sent = mail($to, $subject, $message, implode("\r\n", $headers));

      return $sent;
}

function connected() {

  global $db;
  global $headers;

  $token = $headers['token'];
  if (empty($token)) {
    return array("status" => false, "error" => "no_token", "headers" => $headers);
  }

  $req = $db->prepare('SELECT * FROM tokens WHERE token = ?;');
  $req->execute(array($token));
  $test = $req->fetch();

  if ($test) {
    $date = date('Y-m-d H:i:s');
    if ($date < $test['expiration']) {
      $req2 = $db->prepare('SELECT * FROM users WHERE id = ?;');
      $req2->execute(array($test['user']));
      $test2 = $req2->fetch();
      return array("status" => true, "data" => $test2);
    } else {
      return array("status" => false, "error" => "token_expired");
    }
  } else {
    return array("status" => false, "error" => "invalid_token");
  }

}

