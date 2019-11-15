<?php
require_once(__DIR__ .'/../includes/autoload.php'); 
$actions = new databaseCL; 

// Type 1: Block or Unblock user
// Type 0: View block state

if ($_POST['feedback'] == 0) {
	echo $actions->manageBlock($_POST['id'], 1, 0, $_POST['feedback'])['icon'];
} elseif ($_POST['feedback'] == 1) {
	echo $actions->manageBlock($_POST['id'], 1, 0, $_POST['feedback'])['link'];
} elseif ($_POST['feedback'] == 2) {
	echo $actions->manageBlock($_POST['id'], 1, 0, $_POST['feedback'])['link_icon'];
}
 
// $_POST['type'] 
