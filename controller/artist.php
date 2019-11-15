<?php
 
function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$artist_id = isset($_GET['artist']) ? $_GET['artist'] : (isset($_GET['id']) ? $_GET['id'] : '');
    $artist = $framework->userData($artist_id, 1);

    if ($artist) {

		$PTMPL['user_id'] = $artist['uid'];
		$PTMPL['profile_photo'] = getImage($artist['photo'], 1, 1);
		$PTMPL['cover_photo'] = (($artist['cover']) && ($artist['photo'])) ? getImage($artist['cover'], 1, 1) : getImage($artist['photo'], 1, 1);
		$PTMPL['introduction'] = $artist['intro'] ? $artist['intro'] : 'New User';
		$PTMPL['user_role'] = $role = $framework->userRoles($artist['role']);
		$PTMPL['fullname'] = $artist['fname'].' '.$artist['lname'];
		$PTMPL['verified'] = $artist['verified'] ? ' is-verified' : '';
	    $PTMPL['secondary_navigation'] = secondaryNavigation($artist['uid']);

		$_albums = $databaseCL->fetchAlbum($artist['uid'], 1);
		if ($_albums) {
			$_albums = $_albums;
		} else {
			$_albums = $databaseCL->listLikedItems($artist['uid'], 1);
		}
		if ($_albums) {
			$PTMPL['list_albums'] = artistAlbums($artist['uid']);
		} else {
			$PTMPL['list_albums'] = notAvailable('This '.$role.' has no albums yet', 'no-padding ');
		}

		// Count and show the sidebar follows
		$follower_c = $databaseCL->fetchFollowers($artist['uid'], 1);
		$follower_f = $databaseCL->fetchFollowers($artist['uid'], 2);
	    $PTMPL['count_followers'] = $follower_c ? count($follower_c) : 0;
	    $PTMPL['count_following'] = $follower_f ? count($follower_f) : 0;  
	    $PTMPL['followers'] = showFollowers($artist['uid'], 1);
	    $PTMPL['following'] = showFollowers($artist['uid'], 2);

		// Show the count and follow button of followers
		$PTMPL['followers_display'] = display_likes_follows(null, $artist['uid']); 
	    $PTMPL['follow_btn'] = clickFollow($artist['uid'], $user['uid']);

		$databaseCL->username = $artist['username'];
		$databaseCL->fname = $artist['fname'];
		$databaseCL->lname = $artist['lname'];
		$databaseCL->label = $artist['label'];
		$PTMPL['related'] = relatedItems(2, $artist['uid']);
	 
	    $track_list = $databaseCL->fetchTracks($artist['uid']);
	    if ($track_list) {
	    	$track_list = $track_list;
	    } else {
	    	$track_list = $databaseCL->listLikedItems($artist['uid'], 2);
	    }
	    $list_tracks = '';
	    if ($track_list) {
	    	$n = 0;
	    	foreach ($track_list as $rows) {
	    		$n++;
	    		$list_tracks .= listTracks($rows, $n, 1);
	    	} 
	    } else {
	        $list_tracks = notAvailable('This '.$role.' has no singles yet', 'no-padding '); 
	    }
	    $PTMPL['list_tracks'] = $list_tracks;

	    $PTMPL['most_popular'] = mostPopular($artist['uid']);

	    // Artist stats
	    $track_list = $databaseCL->fetchTracks($artist['uid'], 5); 
	    $databaseCL->track_list = implode(',', $track_list);
	    $fetch_stats = $databaseCL->fetchStats(null, $artist['uid'])[0];
	    if ($fetch_stats['last_month']) {
		    $PTMPL['total_monthly_views'] = '
		    <div class="artist__listeners">
		      <div class="artist__listeners__count">'.$marxTime->numberFormater($fetch_stats['last_month']).'</div>
		      <div class="artist__listeners__label">'.$LANG['monthly_listeners'].'</div>
		    </div>';
	    }
	    if ($track_list) {
		    $PTMPL['show_monthly_viewers'] = showViewers();
	    }

    } else {
    	$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=homepage&notice=true&response=403'), 1);
    }


	// Set the active landing page_title 
	$theme = new themer('artists/artist');
	return $theme->make();
}
?>
