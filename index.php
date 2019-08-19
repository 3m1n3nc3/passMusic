<?php
require_once(__DIR__ . '/includes/autoload.php'); 
 
if(isset($_GET['page']) && isset($action[$_GET['page']])) {
	$page_name = $action[$_GET['page']];
} else {
	$page_name = 'homepage';
} 
 
require_once("controller/{$page_name}.php");  

$PTMPL['site_title'] = 'Passcolabs'; 
$PTMPL['site_url'] = $SETT['url'];
$PTMPL['favicon'] = 'favicon.ico';

$captcha_url = '/includes/vendor/goCaptcha/goCaptcha.php?gocache='.strtotime('now');
$PTMPL['captcha_url'] = $SETT['url'].$captcha_url;

//$PTMPL['token'] = $_SESSION['token_id'];  
  
$PTMPL['language'] = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : '';

// Show the list of playlists
$PTMPL['show_playlists'] = showPlaylist($user['uid']);

// Dynamically included pages
$PTMPL['header'] = globalTemplate(1);
$PTMPL['player'] = globalTemplate(2);
$PTMPL['sidebar'] = globalTemplate(3);
$PTMPL['right_sidebar'] = globalTemplate(4);

// Render the page
$PTMPL['content'] = mainContent();   

$theme = new themer('container');
echo $theme->make();
 
?>
