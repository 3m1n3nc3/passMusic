<?php
	require_once(__DIR__ . '/includes/autoload.php'); 
	 
	if(isset($_GET['page']) && isset($action[$_GET['page']])) {
		$page_name = $action[$_GET['page']];
	} else {
		$page_name = 'homepage';
	} 
	 
	require_once("controller/{$page_name}.php");  

	$PTMPL['site_title'] = $configuration['site_name']; 
	$PTMPL['site_url'] = $SETT['url'];
	$PTMPL['template_url'] = $PTMPL['template_url'];
	$PTMPL['site_logo'] = getImage($configuration['intro_logo'], 1);
	$PTMPL['favicon'] = getImage($configuration['intro_logo'], 1);

	$captcha_url = '/includes/vendor/goCaptcha/goCaptcha.php?gocache='.strtotime('now');
	$PTMPL['captcha_url'] = $SETT['url'].$captcha_url;

	//$PTMPL['token'] = $_SESSION['token_id'];  
	  
	$PTMPL['language'] = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : ''; 

	// Show the list of playlists
	$PTMPL['show_playlists'] = showPlaylist($user['uid']);

	// Set global links
	$PTMPL['new_release_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=new_release');
	$PTMPL['all_releases_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases');


	$PTMPL['site_copy'] = '&copy; Copyright '.date('Y').' <strong><a href="'.$PTMPL['site_url'].'">'.$configuration['site_name'].'</a><strong>. All Rights Reserved';  
	$PTMPL['site_address'] = $configuration['site_office'];
	$PTMPL['site_email'] = $configuration['email'];
	$PTMPL['site_phone'] = $configuration['site_phone'];

	// Dynamically included pages
	if (getPage($page_name) == 'distribution' || getPage($page_name) == 'static') {
		$theme = new themer('distro_container'); 
		$PTMPL['super_header'] = superGlobalTemplate(2);//superGlobalTemplate(1);
		$PTMPL['super_footer'] = superGlobalTemplate(3);
	} elseif (getPage($page_name) == 'admin') {
		$PTMPL['skin'] = ' class="black-skin"'; 
		$theme = new themer('admin_container');
	} else {
		$PTMPL['player'] = globalTemplate(2);
		$PTMPL['sidebar'] = globalTemplate(3);
		$PTMPL['right_sidebar'] = globalTemplate(4);
		$PTMPL['global_playlister'] = playlistManager(1);
		$PTMPL['header'] = globalTemplate(1);
		$theme = new themer('container'); 
	}
	// End Dynamically included pages  

	// Render the page
	$PTMPL['content'] = mainContent();  

	echo $theme->make();
 
?>
