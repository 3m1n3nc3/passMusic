<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$messaging = new social;
$status = $msg = '';

$uid = $_POST['receiver'];
$get_sender = isset($admin) ? $framework->userData($admin['admin_user'], 1) : $framework->userData($user['uid'], 1);

// Set the message variables
$subject = $_POST['subject']; 
$message = $_POST['message'];
$type = $_POST['type'];  

// Set the senders data
$framework->sender_username = ucfirst($get_sender['username']);
$framework->sender_firstname = ucfirst($get_sender['fname']);
$framework->sender_lastname = ucfirst($get_sender['lname']);

if ($uid) {
	$get_user = $framework->userData($uid, 1);
	$receiver_uid = $get_user['uid']; 
} else {
	$list_uid = $framework->userData(0, 0); 
    if($list_uid) { 
    	foreach ($list_uid as $uids) { 
    		$receiver_uid[] = $uids['uid'];
    	}
    }	
}

// Set the site notification message template
$framework->username = ucfirst($get_user['username']);
$framework->firstname = ucfirst($get_user['fname']);
$framework->lastname = ucfirst($get_user['lname']);
$notification_message = $framework->message_template($message); 
$notification_template = '<b>'.$configuration['site_name'].': '.$subject.'</b><br>'.$notification_message;
$messaging->notification = $notification_template;

if (strlen($subject) < 5) {
	$status = 0;
	$msg = messageNotice('<b>Notice: </b> Your subject is too short');
} 
elseif (strlen($message) < 10) {
	$status = 0;
	$msg = messageNotice('<b>Notice: </b> Your message is too short');
} 
else { 
	// Send notification to one user
	if ($type == 2) {
		// Send a site notification 
      	$messaging->sender = $admin['admin_user'];
      	$messaging->receiver = $receiver_uid;
      	$status = $messaging->sendNotification();

		if ($status[0] == 1) {
			$count = $status[1] > 0 ? ' to '.$status[1].' users!' : '!';
			$msg .= messageNotice('Notification Message has been sent'.$count, 1);
		} else {
			$msg .= messageNotice('<b>Error: </b>'.$status, 3);
		}
	} elseif ($type == 1) {
		$msg .= messageNotice(sendEmailMessage(), 1);
	} else {
		// Send a site notification then Send an email message

		// Send the email 
		$msg .= messageNotice(sendEmailMessage(), 1);

		// Send the site notification
      	$messaging->sender = $admin['admin_user'];
      	$messaging->receiver = $receiver_uid;
      	$status = $messaging->sendNotification();

      	// Show the status
		if ($status[0] == 1) {
			$count = $status[1] > 0 ? ' to '.$status[1].' users!' : '!';
			$msg .= messageNotice('Notification Message has been sent'.$count, 1);
		} else {
			$msg .= messageNotice('<b>Error: </b>'.$status, 3);
		}
	} 
}

/**
 * [sendEmailMessage this function helps remove repeating 
 * the send email requests for the multiple times it is required
 * @return string the message to show the user after successfully sending message
 */
function sendEmailMessage() {
	global $SETT, $framework, $uid, $get_user, $list_uid, $message, $subject;  

	// Send an email message
	$msg = '';
	$framework->content = $message;  
	if ($uid) {
		// Set the email message template with receivers data
		$receiver_email = $get_user['email'];
		$framework->user_id = $get_user['uid'];
		$framework->username = ucfirst($get_user['username']);
		$framework->firstname = ucfirst($get_user['fname']);
		$framework->lastname = ucfirst($get_user['lname']);
		$framework->message = $framework->message_template($framework->emailTemplate()); 
		$msg .= $framework->mailerDaemon($SETT['email'], $receiver_email, $subject);
		$msg = 'Email Message has been sent! ';
	} else {
		// If there is more than one user send the message to all of them 
	    $receiver_email = []; $i = 0;
	    if($list_uid) { 
	    	foreach ($list_uid as $uids) {
	    		if (!$uids['newsletter']) {
	    			$i++;
	    			// Set the receivers email address
	    			$receiver_email = $uids['email'];

					// Set the email message template with multiple receivers data
					$framework->user_id = $uids['uid'];
					$framework->username = ucfirst($uids['username']);
					$framework->firstname = ucfirst($uids['fname']);
					$framework->lastname = ucfirst($uids['lname']);  
					$framework->message = $framework->message_template($framework->emailTemplate());  
					$msg .= $framework->mailerDaemon($SETT['email'], $receiver_email, $subject);
	    		}   
	    	}
			$msg = 'Email Message has been sent to '.($i>1 ? $i.' users! ' : $i.' user! '); 
	    }	
	}
	$status = 1;	
	return $msg;
}

$data = array('status' => $status, 'msg' => $msg);
echo json_encode($data, JSON_UNESCAPED_SLASHES); 
