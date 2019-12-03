<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/Mp3Info_master/src/Mp3Info.php'); 
require_once(__DIR__ . '/PHPMailer/Exception.php');
require_once(__DIR__ . '/PHPMailer/PHPMailer.php');
require_once(__DIR__ . '/PHPMailer/SMTP.php');
require_once(__DIR__ . '/imageResize.php'); 
require_once(__DIR__ . '/fontAwesome.php'); 
require_once(__DIR__ . '/intlCurrency.php'); 
require_once(__DIR__ . '/marxtime.class.php'); 
require_once(__DIR__ . '/goCaptcha/goCaptcha.php');
require_once(__DIR__ . '/Twilio/autoload.php'); 
require_once(__DIR__ . '/Paystack/src/autoload.php'); 
require_once(__DIR__ . '/getID3-master/getid3/getid3.php'); 
require_once(__DIR__ . '/CI_Emulator/CI_Class.php');  

// Create the mail object of class
$mail = new PHPMailer;
