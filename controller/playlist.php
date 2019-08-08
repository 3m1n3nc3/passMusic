<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $framework, $databaseCL; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$get_playlist = isset($_GET['playlist']) ? $_GET['playlist'] : '';
	$fetch_playlist = $databaseCL->fetchPlaylist($get_playlist)[0];

	$PTMPL['playlist_title'] = $fetch_playlist['title'];
	$PTMPL['playlist_author'] = $fetch_playlist['fname'].' '.$fetch_playlist['lname'];  
	$PTMPL['playlist_link'] = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist='.$fetch_playlist['plid']);
	$PTMPL['like_id'] = $fetch_playlist['id'];

	// Check if user likes this playlist
	$databaseCL->like = 'single';
	$likes = $databaseCL->userLikes(1, $fetch_playlist['id'], 1);//change (1, $t'id'], 1) > (user_id, $t['id'], 1)

	$PTMPL['liked'] = $likes ? ' text-danger' : '';

	$databaseCL->user_id = $user['uid']; 
	$get_playlist = $databaseCL->playlistEntry($fetch_playlist['id']); 
	$featured = $databaseCL->playlistEntry($fetch_playlist['id'], 1)[0]; 

	$play_class = '<button class="button-dark" id="top-play-btn"> <i class="ion-ios-play"></i> Play </button>';
	$play_playlist = getTrack($featured, $play_class);

	$PTMPL['play_playlist'] = $featured ? $play_playlist : $play_class;
	$PTMPL['playlist_art'] = $PTMPL['cover_photo'] = getImage($featured['art'], 1, 1);

	$n = 0;
	if ($get_playlist) {
		$track = $playlist = '';
		foreach ($get_playlist as $_track) {
			$n++;
			$track .= listTracks($_track, $n);
		}
		$PTMPL['playlist'] = $track;
	} else {
		$PTMPL['playlist'] = notAvailable('This playlist has no tracks');
	}

	$databaseCL->type = 1;
	$count_tracks = $databaseCL->playlistEntry($fetch_playlist['id'])[0]['track_count'];
	$PTMPL['count_tracks'] = $count_tracks;

	$PTMPL['blur_filter'] = 'filter: blur(18px);
       -webkit-filter: blur(18px);';

    // Check for related playlists 
    $PTMPL['related_playlists'] = relatedItems(4, $fetch_playlist['id']); 

    $PTMPL['artist_card'] = artistCard($fetch_playlist['by']);


	// Set the active landing page_title 
	$theme = new themer('music/playlist');
	return $theme->make();
}
?>
