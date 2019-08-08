<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $framework, $databaseCL; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$artist_id = isset($_GET['artist']) ? $_GET['artist'] : (isset($_GET['id']) ? $_GET['id'] : '');
    $artist = $framework->userData($artist_id, 1);

	$PTMPL['user_id'] = $artist['uid'];
	$PTMPL['profile_photo'] = getImage($artist['photo'], 1, 1);
	$PTMPL['cover_photo'] = getImage($artist['cover'], 1, 1);
	$PTMPL['introduction'] = $artist['intro'] ? $artist['intro'] : 'New User';
	$PTMPL['user_role'] = $role = $framework->userRoles($artist['role']);
	$PTMPL['fullname'] = $artist['fname'].' '.$artist['lname'];
	$PTMPL['verified'] = $artist['verified'] ? ' is-verified' : '';

	$_albums = $databaseCL->fetchAlbum($artist['uid'], 1);
	if ($_albums) {
		$PTMPL['list_albums'] = artistAlbums($artist['uid']
);
	} else {
		$PTMPL['list_albums'] = notAvailable('This '.$role.' has no albums yet', 'no-padding ');
	}
	

	$databaseCL->username = $artist['username'];
	$databaseCL->fname = $artist['fname'];
	$databaseCL->lname = $artist['lname'];
	$databaseCL->label = 'newnify';
	$PTMPL['related'] = relatedItems(2, $artist['uid']
);
 
    $track_list = $databaseCL->fetchTracks($artist['uid']
);
    $list_tracks = '';
    if (is_array($track_list) && COUNT($track_list)>0) {
    	$n = 0;
    	foreach ($track_list as $rows) {
    		$n++;
    		$list_tracks .= listTracks($rows, $n, 1);
    	} 
    } else {
        $list_tracks = notAvailable('This '.$role.' has no singles yet', 'no-padding ');
    }
    $PTMPL['list_tracks'] = $list_tracks;

    $PTMPL['most_popular'] = mostPopular($artist['uid']
);

    $PTMPL['followers'] = showFollowers($artist['uid'], 1);

	// Set the active landing page_title 
	$theme = new themer('music/listen');
	return $theme->make();
}
?>
