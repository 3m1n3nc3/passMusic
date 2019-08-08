<?php

function mainContent() {
	global $PTMPL, $user, $LANG, $SETT, $framework, $databaseCL; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$get_album_id = isset($_GET['album']) ? $_GET['album'] : (isset($_GET['id']) ? $_GET['id'] : '');
	$fetch_album = $databaseCL->fetchAlbum($get_album_id)[0];

	$PTMPL['album_title'] = $fetch_album['title'];
	$PTMPL['album_author'] = $fetch_album['fname'].' '.$fetch_album['lname'];
	$PTMPL['album_art'] = $PTMPL['cover_photo'] = getImage($fetch_album['art'], 1, 1);
	$PTMPL['album_desc'] = $fetch_album['description'];
	$PTMPL['album_pline'] = $fetch_album['pline'];
	$PTMPL['album_cline'] = $fetch_album['cline'];
	$PTMPL['album_date'] = date('Y-m-d', strtotime($fetch_album['release_date']));
	$PTMPL['album_year'] = date('Y', strtotime($fetch_album['release_date']));
	$PTMPL['album_link'] = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$fetch_album['safe_link']);
	$PTMPL['like_id'] = $fetch_album['id'];

	// Check if user likes this album
	$databaseCL->like = 'single';
	$likes = $databaseCL->userLikes(1, $fetch_album['id'], 1);//change (1, $t'id'], 1) > (user_id, $t['id'], 1)

	$PTMPL['liked'] = $likes ? ' text-danger' : '';

	$databaseCL->user_id = $user['uid'];
	$get_album = $databaseCL->albumEntry($fetch_album['id']); 
	$get_t1 = $databaseCL->albumEntry($fetch_album['id'], 1)[0]; 

	$play_class = '<button class="button-dark"> <i class="ion-ios-play"></i> Play </button>';
	$play_album = getTrack($get_t1, $play_class);
	$PTMPL['play_album'] = $get_t1 ? $play_album : $play_class;

	$n = 0;
	if ($get_album) {
		$track = $album = '';
		foreach ($get_album as $_track) {
			$n++;
			$track .= listTracks($_track, $n);
		}
		$PTMPL['album'] = $track;
	} else {
		$PTMPL['album'] = notAvailable('This album has no tracks');
	}

	$databaseCL->type = 1;
	$count_tracks = $databaseCL->albumEntry($fetch_album['id'])[0]['track_count'];
	$PTMPL['count_tracks'] = $count_tracks;

	$PTMPL['blur_filter'] = 'filter: blur(18px);
       -webkit-filter: blur(18px);';

    // Check for related albums 
    $PTMPL['related_albums'] = relatedItems(1, $fetch_album['id']); 

    // Show tags
    $PTMPL['showtags'] = showTags($fetch_album['tags']);

    $PTMPL['artist_card'] = artistCard($fetch_album['by']);


	// Set the active landing page_title 
	$theme = new themer('music/album');
	return $theme->make();
}
?>
