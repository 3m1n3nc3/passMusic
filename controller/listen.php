<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$artist_id = isset($_GET['artist']) ? $_GET['artist'] : (isset($user) ? $user['uid'] : '');
    $artist = $framework->userData($artist_id, 1);
    $databaseCL->personal_id = $personal = !isset($_GET['artist']) ? $user['uid'] : NULL;

	$PTMPL['user_id'] = $artist['uid'];
	$PTMPL['profile_photo'] = getImage($artist['photo'], 1, 1);
	$PTMPL['cover_photo'] = getImage($artist['cover'], 1, 1);
	$PTMPL['introduction'] = $artist['intro'] ? $artist['intro'] : 'New User';
	$PTMPL['user_role'] = $role = $framework->userRoles($artist['role']);
	$PTMPL['fullname'] = $artist['fname'].' '.$artist['lname'];
	$PTMPL['verified'] = $artist['verified'] ? ' is-verified' : '';

	$_albums = $databaseCL->fetchAlbum($artist['uid'], 1);
	if ($_albums) {
		$PTMPL['list_albums'] = artistAlbums($artist['uid']);
	} else {
		$PTMPL['list_albums'] = notAvailable('This '.$role.' has no albums yet', 'no-padding ');
	}
	

	$databaseCL->username = $artist['username'];
	$databaseCL->fname = $artist['fname'];
	$databaseCL->lname = $artist['lname'];
	$databaseCL->label = 'newnify';
	$PTMPL['related'] = relatedItems(2, $artist['uid']);
 
    $track_list = $databaseCL->fetchTracks($artist['uid'], 3);
 
    $list_tracks = '';
    if (is_array($track_list) && COUNT($track_list)>0) {
	    $last_track = array_reverse($track_list)[0];

	    // Count all the associated records
	    $databaseCL->counter = $personal ? '' : " AND tracks.public = '1'";
	    $count = $databaseCL->fetchTracks($artist['uid'], 3)[0]; 
	    $PTMPL['load_more_btn'] = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="0" data-last-personal="'.$personal.'"data-last-artist="'.$artist['uid'].'" data-last-track="'.$last_track['id'].'" class="show-more button-light" id="load-more">Load More</button>' : '';

    	$n = 0;
    	foreach ($track_list as $rows) {
    		$n++;
    		$list_tracks .= trackLister($rows, $n, 1);
    	}
    } else {
        $list_tracks = notAvailable('This '.$role.' has not uploaded any tracks', 'no-padding ');
    }
    $PTMPL['list_tracks'] = $list_tracks;

    $PTMPL['most_popular'] = mostPopular($artist['uid']);

    $PTMPL['count_followers'] = count($databaseCL->fetchFollowers($artist['uid'], 1));
    $PTMPL['count_following'] = count($databaseCL->fetchFollowers($artist['uid'], 2)); 
    $PTMPL['followers'] = showFollowers($artist['uid'], 1);
    $PTMPL['following'] = showFollowers($artist['uid'], 2);

	// Set the active landing page_title 
	$theme = new themer('music/listen');
	return $theme->make();
}
?>
