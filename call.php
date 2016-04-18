<?php
  require("Services/Twilio.php");
  require("database.php");
  
  // require POST request
  if ($_SERVER['REQUEST_METHOD'] != "POST") die;

  // generate "random" 6-digit verification code
  $code = rand(100000, 999999);

  // save verification code in DB with phone number
  // attempts to delete existing entries first
  $number = mysql_real_escape_string($_POST["phone_number"]);
  db(sprintf("DELETE FROM numbers WHERE phone_number='%s'", $number));
  db(sprintf("INSERT INTO numbers (phone_number, verification_code) VALUES('%s', %d)", $number, $code));

  mysql_close();

  // initiate phone call via Twilio REST API    
  // Set our AccountSid and AuthToken 
  $AccountSid = "AC3ee8d37f54ed16ee47fda16dfb679b3c";
  $AuthToken = "15ea0b3305975c6b1cb80a3768807935";
  
  // Instantiate a new Twilio Rest Client 
  $client = new Services_Twilio($AccountSid, $AuthToken);
  $client->account->messages->create(array( 
  'To' => "+8801557390493", 
  'From' => "+17194284856", 
  'Body' => "Varify code :1057",   
));

  try {
    // make call
    $call = $client->account->calls->create(
      '+17194284856',                // Verified Outgoing Caller ID or Twilio number
      $number,                       // The phone number you wish to dial
      'http://example.com/twiml.php' // The URL of twiml.php on your server
    );
  } catch (Exception $e) {
    echo 'Error starting phone call: ' . $e->getMessage();
  }

  // return verification code as JSON
  $json = array();	
  $json["verification_code"] = $code;

  header('Content-type: application/json');
  echo(json_encode($json));
?>