<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $configuration, $framework, $databaseCL; 

    $PTMPL['page_title'] = $LANG['view_artists'];   
	
	$PTMPL['site_url'] = $SETT['url']; 

	$artist_id = isset($_GET['artist']) ? $_GET['artist'] : (isset($user) ? $user['uid'] : '');
    if (!$artist_id) {
        $framework->redirect('account&view=access&login=user&referrer='.urlrecoder($SETT['url'].$_SERVER['REQUEST_URI']));
    }
    $artist = $framework->userData($artist_id, 1);

    $cl = $databaseCL->userLikes($artist_id, 0, 3);
    $databaseCL->limit = true;
    $liked_artists = $databaseCL->userLikes($artist_id, 0, 3);
    $_artists = '';
    if ($liked_artists) { 
    	foreach ($liked_artists as $row) {
    		$_artists .= '<div class="mb-3">'.artistCard($row['artist_id']).'</div>';
    		$last_track = $row['artist_id'];
    	}
        $PTMPL['load_more_btn'] = count($cl) > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="3" data-last-personal="" data-last-artist="'.$artist_id.'" data-last-track="'.$last_track.'" class="show-more button-light" id="load-more">Load More</button>' : '';
    } else {
        $_artists = notAvailable('No artists to show', 'no-padding ');
    }

    $PTMPL['artist_card'] = $_artists;
    $PTMPL['secondary_navigation'] = secondaryNavigation($artist_id);
   
    $PTMPL['followers'] = showFollowers($artist['uid'], 1);
    $PTMPL['following'] = showFollowers($artist['uid'], 2);

    $sidebar = new themer('artists/small_right_sidebar');
    $PTMPL['small_right_sidebar'] = $sidebar->make();

	// Set the active landing page_title 
	$theme = new themer('artists/view_artists');
	return $theme->make();
}
?>
