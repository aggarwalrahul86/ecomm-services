<?php

require 'phpMailer/PHPMailerAutoload.php';
require 'Slim/Slim.php';
//require 'db/crud.php';
//require 'db/list.php';
require 'db/connection.php';

//include 'dbService.php';
\Slim\Slim::registerAutoloader();

define('MYSQL_HOST', 'localhost');

$app = new \Slim\Slim();

/**
 * Routing
 */

$app->post('/authenticate','_doLogin');
$app->post('/sendemail', '_sendEmail');




function _doLogin() {
  $data = json_decode(Slim\Slim::getInstance()->request()->getBody(), true);

     $username =  (isset($data['username']))   ? $data['username'] : null;
     $password =  (isset($data['password']))   ? $data['password'] : null;

	try {
		$db = getDB();
		$stmt = $db->prepare("SELECT * FROM admin WHERE username=:username1 AND password=:password1");
		$stmt->bindValue(':username1', $username, PDO::PARAM_INT);
		$stmt->bindValue(':password1', $password, PDO::PARAM_STR);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$db = null;

		if(count($rows)) {
			echo '{"status": "Success"}';
		}
		else
			echo '{"status": "Incorrect"}';
	} catch(PDOException $e) {
		echo '{"error":{"msg":'. $e->getMessage() .'}}';
	}

}


// Send Email
function _sendEmail(){

$data = json_decode(Slim\Slim::getInstance()->request()->getBody(), true);

$body =  (isset($data['body']))   ? $data['body'] : '';
$from =  (isset($data['from']))   ? $data['from'] : '';
$to =  (isset($data['to']))   ? $data['to'] : '';
$toName =  (isset($data['toName']))   ? $data['toName'] : '';



//Create a new PHPMailer instance
$mail = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail->isSMTP();


//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;

//Ask for HTML-friendly debug output
//$mail->Debugoutput = 'html';

//Set the hostname of the mail server
//$mail->Host = 'smtp.internal.ericsson.com';
$mail->Host = 'smtp.gmail.com';
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = 465;

//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = 'ssl';

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
$mail->Username = "aggarwalrahul86@gmail.com";

//Password to use for SMTP authentication
$mail->Password = "bahezgksudarlazq";

//Set who the message is to be sent from
$mail->setFrom($from, 'Admin');

//Set an alternative reply-to address
//$mail->addReplyTo('replyto@example.com', 'First Last');

//Set who the message is to be sent to
$mail->addAddress($to, $toName);

//Set the subject line
$mail->Subject = 'Query For Products';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

$mail->Body = $body; //HTML Body
$mail->IsHTML(true);
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent";
}

}

$app->run();
