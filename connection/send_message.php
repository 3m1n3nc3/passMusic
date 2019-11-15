<?php
require_once(__DIR__ .'/../includes/autoload.php');
$messaging = new social;

echo $messaging->send_message($_POST['receiver'], $_POST['message']); 
  
