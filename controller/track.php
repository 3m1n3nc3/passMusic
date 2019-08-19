<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url'];

	$databaseCL->track = isset($_GET['track']) ? $_GET['track'] : (isset($_GET['id']) ? $_GET['id'] : '');
	$track = $databaseCL->fetchTracks(null, 2)[0];

	$PTMPL['track_link'] = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$track['safe_link']);
	$PTMPL['track_author_link'] = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$track['username']);
	$PTMPL['track_art'] = $PTMPL['cover_photo'] = getImage($track['art'], 1, 1); 
 
	$PTMPL['track_title'] = $track['title'];
	$PTMPL['track_author'] = $track['fname'].' '.$track['lname'];
	$PTMPL['track_desc'] = $track['description'];
	$PTMPL['track_pline'] = $track['pline'];
	$PTMPL['track_cline'] = $track['cline'];
	$PTMPL['track_date'] = date('Y-m-d', strtotime($track['release']));
	$PTMPL['track_year'] = date('Y', strtotime($track['release']));

    $count_views = $databaseCL->fetchStats(1, $track['id'])[0]; 
    $PTMPL['track_views'] = $marxTime->numberFormater($count_views['total'], 1);

	$PTMPL['likes_display'] = display_likes_follows(3, $track['id']);
	$PTMPL['like_id'] = $track['id'];
	$PTMPL['track_audio'] = getAudio($track['audio']);
	$PTMPL['format'] = strtolower(pathinfo($track['audio'], PATHINFO_EXTENSION));
 
	$PTMPL['like_button'] = clickLike(2, $track['id'], $user['uid']);

	$PTMPL['blur_filter'] = '
		filter: blur(18px);
       -webkit-filter: blur(18px);';

    // Show tags
    $PTMPL['showtags'] = showTags($track['tags']);

    $PTMPL['artist_card'] = artistCard($track['artist_id']);

	$play_class = '<button class="button-dark" id="top-play-btn"> <i class="ion-ios-play jp-play"></i> Play </button>';
	$play_track = getTrack($track, $play_class);
	$PTMPL['play_track'] = $track ? $play_track : $play_class;

	// Fetch similar tracks
	$PTMPL['related_tracks'] = relatedItems(3, $track['id']);

	// Show the track stat
    $databaseCL->limit = $configuration['page_limits'];
    $PTMPL['sidebar_statistics'] = sidebarStatistics($track['id'], 1);

    // Show the secondary navigation bar
    $PTMPL['secondary_navigation'] = secondaryNavigation($track['artist_id']);

	// Show likes for this track
	if (isset($_GET['likes'])) {
		$PTMPL['content_title'] = '<div class="section-title">Users who like '.$track['title'].'</div>';
		$item_id = $track['id'];
		$databaseCL->type = 2;
		$databaseCL->limit = false;
	    $cl = $databaseCL->userLikes(null, $item_id, 5);
	    $databaseCL->limit = true;
	    $likers = $databaseCL->userLikes(null, $item_id, 5);
	    $_items = '';
	    if ($likers) { 
	    	$count = count($cl);
	    	foreach ($likers as $row) { 
	    		$_items .= '<div class="mb-3">'.artistCard($row['artist_id']).'</div>';
	    		$last_items = $row['time'];
	    	}
	        $PTMPL['load_more_btn'] = $count > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="5" data-last-personal="" data-last-artist="'.$item_id.'" data-last-track="'.$last_items.'" class="show-more button-light" id="load-more">Load More</button>' : '';
	    }

	    $PTMPL['artist_card'] = $_items;
		$theme = new themer('artists/view_artists');
	} else {
		$theme = new themer('music/track');
	}
	return $theme->make();
}
?>
