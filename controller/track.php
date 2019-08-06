<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $framework, $databaseCL; 

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
    $PTMPL['track_views'] = number_format($track['views']);
	$PTMPL['like_id'] = $track['id'];
	$PTMPL['track_audio'] = getAudio($track['audio']);

	$databaseCL->like = 'single';
	$likes = $databaseCL->userLikes(1, $track['id'], 2); //change (1, $t'id'], 2); to (user_id, $t['id'], 2);

	$PTMPL['liked'] = $likes ? ' text-danger' : '';

	$PTMPL['blur_filter'] = '
		filter: blur(18px);
       -webkit-filter: blur(18px);';

    // Show tags
    $PTMPL['showtags'] = showTags($track['tags']);

    $PTMPL['artist_card'] = artistCard($track['artist_id']);

	$play_class = '<button class="button-dark"> <i class="ion-ios-play"></i> Play </button>';
	$play_track = getTrack($track, $play_class);
	$PTMPL['play_track'] = $track ? $play_track : $play_class;

	// Fetch similar tracks
	$PTMPL['related_tracks'] = relatedItems(3, $track['id']);

	// Set the active landing page_title 
	$theme = new themer('music/track');
	return $theme->make();
}
?>
