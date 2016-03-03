<?php

require 'phpMailer/PHPMailerAutoload.php';
require 'Slim/Slim.php';
require 'mongo/crud.php';
require 'mongo/list.php';
require 'mongo/command.php';

//include 'dbService.php';
\Slim\Slim::registerAutoloader();

define('MONGO_HOST', 'localhost');

$app = new \Slim\Slim();

/**
 * Routing
 */
$app->post('/sendemail', '_sendEmail');
$app->get(    '/:db/:collection',      '_list');
$app->post(   '/:db/:collection',      '_create');
$app->post(   '/authenticate/:db',      '_authenticate');
$app->get(    '/:db/:collection/:id',  '_read');
$app->put(    '/:db/:collection/:id',  '_update');
$app->delete( '/:db/:collection/:id',  '_delete');


// @todo: add count collection command mongo/commands.php

// List

function _list($db, $collection){


  $select = array(
    'limit' =>    (isset($_GET['limit']))   ? $_GET['limit'] : false,
    'page' =>     (isset($_GET['page']))    ? $_GET['page'] : false,
    'filter' =>   (isset($_GET['filter']))  ? $_GET['filter'] : false,
    'regex' =>    (isset($_GET['regex']))   ? $_GET['regex'] : false,
    'sort' =>     (isset($_GET['sort']))    ? $_GET['sort'] : false
  );

  $data = mongoList(
    MONGO_HOST,
    $db,
    $collection,
    $select
  );
  header("Content-Type: application/json");
  echo json_encode($data);
  exit;
}

//Create

function _create($db, $collection){

  $document = json_decode(Slim\Slim::getInstance()->request()->getBody(), true);

  $data = mongoCreate(
    MONGO_HOST,
    $db,
    $collection,
    $document
  );
  header("Content-Type: application/json");
  echo json_encode($data);
  exit;
}

// Authenticate

function _authenticate($db){

  $data = json_decode(Slim\Slim::getInstance()->request()->getBody(), true);

     $username =  (isset($data['username']))   ? $data['username'] : null;
     $password =  (isset($data['pwd']))   ? $data['pwd'] : null;


   if(!empty($username) and !empty($password))
   {

      if(mongoCollectionCount(MONGO_HOST, $db, 'users',  $data ) >=1){

        $array = array("status" => "Login Successful");
      }else{
       $array = array("status" => "Credentials are Incorrect");
      }
}else{
  $array = array("status" => "Data not recieved.");
 }

  header("Content-Type: application/json");
   echo json_encode($array);
  exit;
}

// Read

function _read($db, $collection, $id){

  $data = mongoRead(
    MONGO_HOST,
    $db,
    $collection,
    $id
  );
  header("Content-Type: application/json");
  echo json_encode($data);
  exit;
}

// Update

function _update($db, $collection, $id){

  $document = json_decode(Slim::getInstance()->request()->getBody(), true);

  $data = mongoUpdate(
    MONGO_HOST,
    $db,
    $collection,
    $id,
    $document
  );
  header("Content-Type: application/json");
  echo json_encode($data);
  exit;
}

// Delete

function _delete($db, $collection, $id){

  $data = mongoDelete(
    MONGO_HOST,
    $db,
    $collection,
    $id
  );
  header("Content-Type: application/json");
  echo json_encode($data);
  exit;
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

//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "<p>Message successfully sent!</p>";
}

}

$app->run();
