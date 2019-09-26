<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $configuration, $framework, $databaseCL; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$artist_id = isset($_GET['artist']) ? $_GET['artist'] : (isset($user) ? $user['uid'] : '');
    $artist = $framework->userData($artist_id, 1);

    $t = $user['uid'] == $artist_id ? 'you' : ucfirst($artist['fname']. ' '.$artist['lname']);
    $s = $user['uid'] == $artist_id ? '' : '\'s';
    if (isset($_GET['get']) && $_GET['get'] == 'followers') {
        // Fetch followers
        $type = 1;
        $PTMPL['follow_title'] = 'See people following '.$t;
    } else {
        // Fetch following
        $type = 2;
        $PTMPL['follow_title'] = 'See people '.$t.' follow'.$s;
    }
 
    $databaseCL->limit = true;
    $follows = $databaseCL->fetchFollowers($artist_id, $type); 
    $follow_cards = '';
    if ($follows) {
        foreach ($follows as $rows) {
            $follow_cards .= followCards($rows);
            $last_id = $rows['order_id'];
        }
        $follows_count = $databaseCL->fetchFollowers($artist_id, $type);
        $PTMPL['load_more_btn'] = count($follows_count) > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="4" data-last-personal="'.$type.'" data-last-artist="'.$artist_id.'" data-last-track="'.$last_id.'" class="show-more button-light" id="load-more">Load More</button>' : '';
    } else {
        $ts = $user['uid'] == $artist_id ? 'you have' : ucwords($artist['fname']. ' '.$artist['lname']).' has';
        $nmsg = isset($_GET['get']) && $_GET['get'] == 'following' ? sprintf($LANG['no_following'], $ts) : sprintf($LANG['no_followers'], $ts);
        if (isset($_GET['get']) && $_GET['get'] == 'following') {
            if ($user['uid'] == $artist_id) {
                $nmsg = $LANG['you_no_following'];
            } else {
                $nmsg = sprintf($LANG['no_following'], ucwords($artist['fname']. ' '.$artist['lname']));
            }
        } else {
            if ($user['uid'] == $artist_id) {
                $nmsg = $LANG['you_no_followers'];
            } else {
                $nmsg = sprintf($LANG['no_followers'], ucwords($artist['fname']. ' '.$artist['lname']));
            } 
        }
        $PTMPL['no_followers'] = notAvailable($nmsg);
    }
 
    $PTMPL['secondary_navigation'] = secondaryNavigation($artist_id);

    $PTMPL['followers_media_card'] = $follow_cards; 

    // Fetch suggested users 
    $PTMPL['sidebar_suggested_users'] = sidebar_userSuggestions($artist_id); 
     
	$theme = new themer('artists/follow');
	return $theme->make();
}
?>
