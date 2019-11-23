<?php
require_once(__DIR__ .'/../includes/autoload.php'); 
$messaging = new social; 

if (isset($_POST['type'])) {
	if ($_POST['type'] == 1) {
		// Fetch the new message
	  	echo $messaging->messenger(2, $_POST['user_id']);
	} else {
		// Fetch the new notification
		if (isset($_POST['view']) && $_POST['view'] == 1) {
			$databaseCL->fetchNotifications($user['uid'], 1); 
		}
		// Count the unread messages
		$messaging->seen = 0;
		$msgs = $messaging->fetchMessages(6);
		$new_msg = $msgs ? count($msgs) : 0;
		$count_new_msg = $new_msg ? '<span class="counter">'.$new_msg.'</span>' : '';

		// Show the unread messages 
		$messages = $new_msg ? showNotifications(1, 1) : notAvailable('No new messages!', ' ', 3);

		// Count the new notifications
		$databaseCL->seen = isset($_POST['view']) ? $_POST['view'] : null;
		$new_noti = $framework->dbProcessor(sprintf("SELECT COUNT(`id`) AS new FROM notification WHERE `uid` = '%s' AND `status` = '0'", $user['uid']), 1)[0]['new'];
		$count_new_noti = $new_noti ? '<span class="counter">'.$new_noti.'</span>' : '';

		// Show all notifications
		$all_noti = $framework->dbProcessor(sprintf("SELECT COUNT(`id`) AS count_all FROM notification WHERE `uid` = '%s'", $user['uid']), 1)[0]['count_all'];
		$notifications = $all_noti ? showNotifications(1) : notAvailable('No notifications!', ' ', 3);;

		echo json_encode(array(
			'notifications' => $notifications, 
			'messages' => $messages, 
			'count_noti' => $count_new_noti,
			'count_msg' => $count_new_msg
		), JSON_UNESCAPED_SLASHES);
	}
} elseif (!empty($_POST['search'])) {
	// Search your subscribers 
	echo $messaging->activeChats($user['uid'], 1, $_POST['q']);
} else {
	// Load more messages
  	$messaging->chat_id = $_POST['chat_id']; 
  	$messaging->start = $_POST['start'];
  	echo $messaging->messenger(3, $_POST['user_id']);
} 
