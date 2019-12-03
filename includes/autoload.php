<?php 
	if (!file_exists(__DIR__.'/config.php')) {
		header('Location: ../install/licence.php');
		exit;
	}
	require_once(__DIR__ . '/config.php');

	session_set_cookie_params(null, COOKIE_PATH);
	session_start();

	require_once(__DIR__ . '/vendor/autoload.php');
	require_once(__DIR__ . '/environment.php'); 
	require_once(__DIR__ . '/themer.php');
	require_once(__DIR__ . '/constants.php');
	require_once(__DIR__ . '/classes.php');
	require_once(__DIR__ . '/database.php');
	require_once(__DIR__ . '/extend_class.php');
	require_once(__DIR__ . '/misc.php');  
	require_once(__DIR__ . '/countries.php');  
	$framework = new framework; 

	require_once($framework->getLanguage(null, (isset($_GET['lang']) && !empty($_GET['lang']) ? $_GET['lang'] : (isset($_COOKIE['lang']) ? $_COOKIE['lang'] : null)), null));
