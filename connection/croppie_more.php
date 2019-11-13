<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$response = '';  

// fetch user data
$user_id = isset($_GET['id']) ? $_GET['id'] : $user['uid'];

$get_user = $framework->userData($user_id, 1); 

// Check if this upload is ajax
if (isset($_POST['ajax_image'])) {

  	$ajax_image_ = explode(';',$_POST['ajax_image']);

  	$ajax_image_ = isset($ajax_image_[1]) ? $ajax_image_[1] : null; 

}

if ($ajax_image_) {

	$image = $_POST['ajax_image'];

	list($type, $image) = explode(';',$image);

	list(, $image) = explode(',',$image);

	$image = base64_decode($image);

	$new_image = mt_rand().'_'.mt_rand().'_'.mt_rand().'_n.png';

  	// Check what type of photo is being uploaded
  	if (isset($_GET['action'])) { 
    	if ($_GET['action'] == 'profile') {
	      	// Upload the profile photo 
	      	$table = 'photo';
    	} elseif ($_GET['action'] == 'cover') {
      		// Upload the profile photo  
	      	$table = 'cover';    
    	} 
  	}

  	// Save the new image to the upload directory  
  	if (is_writable($SETT['working_dir'].'/uploads/photos/')) {

    	file_put_contents($SETT['working_dir'].'/uploads/photos/'.$new_image, $image); 

	  	// delete the old image
	  	if (isset($get_user[$table])) {

	    	deleteFile($get_user[$table], 1);

	  	}

	  	// Link image to DB 
	  	$query = $framework->dbProcessor(sprintf("UPDATE users SET `%s` = '%s' WHERE uid = '%s'", $table, $new_image, $get_user['uid']), 0, 1);

	  	if ($query == 1) {

	  		$response .= messageNotice(ucfirst($_GET['action']).' photo has been successfully uploaded', 1);

	  	} else {

	  		$response .= messageNotice(ucfirst($_GET['action']).' photo could not be uploaded. <b>Error: </b>'.$query, 3);

	  	}
  	} else {
  		
    	$response .= messageNotice('<b>Error: </b>'.$SETT['working_dir'].'/uploads/'.$upload_dir.' is not writable', 3);

  	} 

} elseif (empty($ajax_image_)) {

  	$response .= messageNotice('<b>Error: </b>Please choose a valid image file');

}

echo $response; 
