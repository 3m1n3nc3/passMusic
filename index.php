<?php
	require_once(__DIR__ . '/includes/autoload.php'); 
	 
	if(isset($_GET['page']) && isset($action[$_GET['page']])) {
		$page_name = $action[$_GET['page']];
	} else {
		$page_name = 'homepage';
	} 
	 
	require_once("controller/{$page_name}.php");  

	$PTMPL['tracking_code'] = $configuration['tracking']; 
	$PTMPL['site_title'] = $configuration['site_name']; 
	$PTMPL['page_name'] = getPage($page_name); 
	$PTMPL['timeout_intval'] = $configuration['timeout_intval'];
	$PTMPL['site_url'] = $SETT['url']; 
	$PTMPL['site_logo'] = getImage($configuration['intro_logo']);
	$PTMPL['favicon'] = getImage($configuration['intro_logo']);

	$captcha_url = '/includes/vendor/goCaptcha/goCaptcha.php?gocache='.strtotime('now');
	$PTMPL['captcha_url'] = $SETT['url'].$captcha_url;
	$PTMPL['login_url'] = cleanUrls($SETT['url'].'/index.php?page=account&view=access&login=user&referrer='.urlrecoder($SETT['url'].$_SERVER['REQUEST_URI']));
	$PTMPL['register_url'] = cleanUrls($SETT['url'].'/index.php?page=account&view=access&login=register&referrer='.urlrecoder($SETT['url'].$_SERVER['REQUEST_URI']));

	//$PTMPL['token'] = $_SESSION['token_id'];  
	  
	$PTMPL['language'] = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : ''; 

	// Show the list of playlists
	$PTMPL['show_playlists'] = showPlaylist(isset($user['uid']) ? $user['uid'] : null);
	$PTMPL['create_playlist_btn'] = ($user ? '
	<a href="#"
		class="playlist-modal-show"
		id="playlist-modal-show"
		data-toggle="modal"
		data-target="#playlistModal"
		onclick="playlist_modal(2, {\'action\': \'c_list\'})">
		<i class="ion-ios-add-circle-outline"></i>
		New Playlist
	</a>' : '
	<a href="'.$PTMPL['login_url'].'">
		<i class="ion-ios-person"></i>
		Login to Create Playlist
	</a>');

	// Fetch random tracks for player preview on sidebar.html
	$databaseCL->filter = " AND `featured` = '1' ORDER BY RAND() LIMIT 1";
	$random_track = $databaseCL->fetchTracks(0, 6)[0];
	if (!$random_track) {
		$databaseCL->filter = " ORDER BY RAND() LIMIT 1";
		$random_track = $databaseCL->fetchTracks(0, 6)[0];
	}
	if ($random_track) { 
		$PTMPL['random_track'] = '
		<div class="playing__art" id="artwork-container">
			<img src="'.getImage($random_track['art'], 1).'" alt="Album Art" />
		</div>
		<div class="playing__song">
			<a href="'.cleanUrls($SETT['url'].'/index.php?page=track&track='.$random_track['safe_link']).'" class="playing__song__name" id="song-name">'.$random_track['title'].'</a>
			<a href="'.cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$random_track['username']).'" class="playing__song__artist" id="author-name">'.$framework->realName($random_track['username'], $random_track['fname'], $random_track['lname']).'</a>
		</div>
		<div class="playing__add">
			<i class="ion-ios-checkmark" style="font-size: 30px;"></i>
		</div>'; 
	}
	
	// Set global links
	$PTMPL['explore_url'] = cleanUrls($SETT['url'] . '/index.php?page=explore');
	$PTMPL['new_release_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=new_release');
	$PTMPL['all_releases_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases');

	$PTMPL['site_copy'] = '&copy; Copyright '.date('Y').' <strong><a href="'.$PTMPL['site_url'].'">'.$configuration['site_name'].'</a><strong>. All Rights Reserved';  
	$PTMPL['site_address'] = $configuration['site_office'];
	$PTMPL['site_email'] = $configuration['email'];
	$PTMPL['site_phone'] = $configuration['site_phone'];

    if (isset($_GET['q']) && $_GET['q'] !== '' && strlen($_GET['q']) >= 3) {
        $PTMPL['search_input'] = $_GET['q'];
    }
    if (isset($_POST['search_input'])) {
        $q = $framework->urlRequery('&q='.urlencode($_POST['search_input']).'&rel=search');
        $framework->redirect($q, 1); 
    }

	// Dynamically included pages
	if (getPage($page_name) == 'distribution' || getPage($page_name) == 'static') {
		$theme = new themer('distro_container'); 
		$PTMPL['super_header'] = superGlobalTemplate(2);//superGlobalTemplate(1);
		$PTMPL['super_footer'] = superGlobalTemplate(3);
	} elseif (getPage($page_name) == 'admin') {
		$PTMPL['skin'] = ' class="black-skin"'; 
		$theme = new themer('admin_container');
	} elseif (getPage($page_name) == 'homepage') { 
		$theme = new themer('homepage/content');
		$PTMPL['super_header'] = superGlobalTemplate(4);//superGlobalTemplate(1);
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
