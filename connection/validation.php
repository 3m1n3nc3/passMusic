<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$response = ''; 

if ($_POST['type'] == 1) {
	$var_user = $framework->userData($_POST['data'], 2); 
	if (!$var_user || $var_user['username'] == $user['username']) {
		$response = '<small class="form-text text-success mb-4"> Username is available </small>';
	} else {
		$response = '<small class="form-text text-danger mb-4"> Username is not available </small>';
	}
} elseif ($_POST['type'] == 2) {
	$var_email = $framework->checkEmail($_POST['data'], 1);
	if (!$var_email || $var_email['email'] == $user['email']) {
		$response = '<small class="form-text text-success mb-4"> Email Address is available </small>';
	} else {
		$response = '<small class="form-text text-danger mb-4"> Email Address is not available </small>';
	}
}
echo $response;
