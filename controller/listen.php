<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL, $marxTime; 

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
    $PTMPL['secondary_navigation'] = secondaryNavigation($artist_id);
 
	if (isset($_GET['to'])) {
		// Listen to tracks
		// 
		if ($_GET['to'] == 'tracks') {
			$PTMPL['favorite_title'] = 'Favorite tracks by '.$artist['username'];
			$track_list = $databaseCL->listLikedItems($artist['uid'], 2);

		    $list_tracks = '';
		    if ($track_list) {
			    $last_track = array_reverse($track_list)[0]; 
		    	$n = 0;

			    // Count all the associated records
			    $databaseCL->counter = true;
			    $count = $databaseCL->listLikedItems($artist['uid'], 2)[0];
			    $PTMPL['load_more_btn'] = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="1" data-last-personal="" data-last-artist="'.$artist['uid'].'" data-last-track="'.$last_track['like_id'].'" class="show-more button-light" id="load-more">Load More</button>' : '';

		    	foreach ($track_list as $rows) {
		    		$n++;
		    		$list_tracks .= trackLister($rows, $n, 1); 
		    	}
		    } else {
		        $list_tracks = notAvailable('No tracks here', 'no-padding ');
		    }
		    $PTMPL['list_tracks'] = $list_tracks; 
		} elseif ($_GET['to'] == 'albums') {
			// Listen to albums
			// 		 
			$PTMPL['favorite_title'] = 'Favorite albums by '.$artist['fname'].' '.$artist['lname'];
			$album_list = $databaseCL->listLikedItems($artist['uid'], 1);

		    $list_albums = '';
		    if ($album_list) {
			    $last_album = array_reverse($album_list)[0]; 
		    	$n = 0;

			    // Count all the associated records
			    $databaseCL->counter = true;
			    $count = $databaseCL->listLikedItems($artist['uid'], 1)[0];
			    $PTMPL['load_more_btn'] = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="2" data-last-personal="" data-last-artist="'.$artist['uid'].'" data-last-track="'.$last_album['like_id'].'" class="show-more button-light" id="load-more">Load More</button>' : '';

		    	foreach ($album_list as $rows) {
		    		$n++;
		    		$list_albums .= albumsLister($rows['by'], $rows);
		    	}
		    } else {
		        $list_albums = notAvailable('No albums here', 'no-padding ');
		    }
		    $PTMPL['list_tracks'] = $list_albums; 
		} elseif ($_GET['to'] == 'artist') {
			// Listen to tracks by the selected artist
			// 		 
			$PTMPL['favorite_title'] = 'Tracks by '.$artist['username'];
    		$artist['uid'] == $user['uid'] ? $databaseCL->personal_id = $personal = $artist['uid'] : '';
			$track_list = $databaseCL->fetchTracks($artist['uid'], 3);

		    $list_tracks = '';
		    if (is_array($track_list) && COUNT($track_list) > 0) {
			    $last_track = array_reverse($track_list)[0];
		    	$n = 0;

			    // Count all the associated records 
			    $databaseCL->counter = true;
				$count = $databaseCL->fetchTracks($artist['uid'], 3)[0];
			    $PTMPL['load_more_btn'] = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="0" data-last-personal="'.$personal.'" data-last-artist="'.$artist['uid'].'" data-last-track="'.$last_track['id'].'" class="show-more button-light" id="load-more">Load More</button>' : '';

		    	foreach ($track_list as $rows) {
		    		$n++;
		    		$list_tracks .= trackLister($rows, $n, 1); 
		    	}
		    } else {
		        $list_tracks = notAvailable('No tracks here', 'no-padding ');
		    }
		    $PTMPL['list_tracks'] = $list_tracks; 
		} elseif ($_GET['to'] == 'artist-album') {
			// Listen to albums
			// 		 
			$PTMPL['favorite_title'] = 'Albums by '.$artist['username'];
			$album_list = $databaseCL->fetchAlbum($artist['uid'], 1);

		    $list_albums = '';
		    if (is_array($album_list) && COUNT($album_list) > 0) {
			    $last_album = array_reverse($album_list)[0]; 
		    	$n = 0;

			    // Count all the associated records
			    $databaseCL->counter = true;
			    $count = $databaseCL->listLikedItems($artist['uid'], 1)[0];
			    $PTMPL['load_more_btn'] = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="2" data-last-personal="" data-last-artist="'.$artist['uid'].'" data-last-track="'.$last_album['like_id'].'" class="show-more button-light" id="load-more">Load More</button>' : '';

		    	foreach ($album_list as $rows) {
		    		$n++;
		    		$list_albums .= albumsLister($rows['by'], $rows);
		    	}
		    } else {
		        $list_albums = notAvailable('No albums here', 'no-padding ');
		    }
		    $PTMPL['list_tracks'] = $list_albums; 
		}
	} 
	$databaseCL->username = $artist['username'];
	$databaseCL->fname = $artist['fname'];
	$databaseCL->lname = $artist['lname'];
	$databaseCL->label = 'newnify';
	$PTMPL['related'] = relatedItems(2, $artist['uid']);
 

	$follower_c = $databaseCL->fetchFollowers($artist['uid'], 1);
	$follower_f = $databaseCL->fetchFollowers($artist['uid'], 2);
    $PTMPL['count_followers'] = $follower_c ? count($follower_c) : 0;
    $PTMPL['count_following'] = $follower_f ? count($follower_f) : 0; 
    $PTMPL['followers'] = showFollowers($artist['uid'], 1);
    $PTMPL['following'] = showFollowers($artist['uid'], 2);

	// Show the track stat
    $databaseCL->limit = $configuration['page_limits'];
    $PTMPL['sidebar_statistics'] = sidebarStatistics($artist['uid'], null);

    // Artist stats
    $track_list = $databaseCL->fetchTracks($artist['uid'], 5);
    $databaseCL->track_list = implode(',', $track_list);
    $fetch_stats = $databaseCL->fetchStats(null, $artist['uid'])[0];
    $PTMPL['total_monthly_views'] = $marxTime->numberFormater($fetch_stats['last_month']);
    $PTMPL['show_monthly_viewers'] = showViewers();

	// Set the active landing page_title 
	$theme = new themer('music/listen');
	return $theme->make();
}
?>
