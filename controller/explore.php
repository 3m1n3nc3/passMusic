<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $configuration, $framework, $databaseCL; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$artist_id = isset($_GET['artist']) ? $_GET['artist'] : (isset($user) ? $user['uid'] : '');
    $artist = $framework->userData($artist_id, 1); 

    // Show the users tracks stats
    $databaseCL->limit = $configuration['page_limits'];

    // Choose to display sets or the explore page
    if (isset($_GET['sets']) && $_GET['sets'] !== '') {
        $type = $_GET['sets'] == 'latest' ? 1 : null;
        $databaseCL->genre = $_GET['go']; 
        $top_tracks = $databaseCL->fetchTopTracks($type);
        
        $track_list = '';
        if ($top_tracks) {
            foreach ($top_tracks as $rows) {  
                $track_list .= trackDetail__card($rows, 1);
            }
        } else {
            $track_list = notAvailable('No result for this query');
        }
        $PTMPL['list_tracks'] = $track_list;  
    } else {

        $explore_bar = '
        <h2 class="intro-header-title">%s</h2>
        <div class="section-title">%s</div> 
        <div class="media-cards more-container scrollable-container">
            <section class="cardet">
                %s
            </section>
        </div>
        <hr>';
        
        // Show top tracks
        $show_top_charts = topTracks();
        $top_charts_sub = 'The most played tracks on PassColab this week';
        $PTMPL['show_top_charts'] = $show_top_charts ? sprintf($explore_bar, 'Charts: Top 50', $top_charts_sub, $show_top_charts) : '';

        // Show new charts
        $show_new_charts = topTracks(1);
        $new_charts_sub = 'Hot Upcoming tracks on PassColab';
        $PTMPL['show_new_tracks'] = $show_new_charts ? sprintf($explore_bar, 'Charts: Latest and Hot', $new_charts_sub, $show_new_charts) : '';

        // Show new artists
        $show_new_users = topTracks(2);
        $new_users_sub = 'Hear top sounds from newest artists';
        $PTMPL['show_new_users'] = $show_new_users ? sprintf($explore_bar, 'Latest Artists', $new_users_sub, $show_new_users) : '';
      
        // Show artists you may know 
        if ($user) {
            $simi = $databaseCL->fetchFollowers($user['uid'])[0];
            $related_artist_sub = 'Top tracks from artists similar to '.$simi['fname'].' '.$simi['lname'];
        } else {
            $related_artist_sub = 'Top selected tracks from artists you may know';
        }
        $you_may_know = topTracks(3); 
        $PTMPL['show_artists_you_may_know'] = $show_new_charts ? sprintf($explore_bar, 'Artists you may know', $related_artist_sub, $you_may_know) : ''; 

        // Songs playlists
        $pl_types = array('slow', 'Dog', 'food', 'way', 'play', 'song');
        if ($pl_types) {
            $playlistster = '';
            foreach ($pl_types as $key => $value) {
                $databaseCL->title = $value;
                $playlist_one = topTracks(4);
                $playlist_one_sub = 'Popular Playlist from our community';
                $playlists = $playlist_one ? sprintf($explore_bar, ucfirst($value), $playlist_one_sub, $playlist_one) : ''; 
                $playlistster .= $playlist_one ? $playlists : '';
            }
            $PTMPL['playlist_one'] = $playlistster;
        }
        $PTMPL['following'] = showFollowers($artist['uid'], 2);
    }

    $PTMPL['sidebar_statistics'] = sidebarStatistics($artist_id);
 
    // Fetch suggested users  
    $PTMPL['sidebar_suggested_users'] = sidebar_userSuggestions($artist_id); 

    // Fetch suggested tracks
    $PTMPL['sidebar_suggested_tracks'] = sidebar_trackSuggestions($artist_id); 

    // Show the secondary navigation bar
    $PTMPL['secondary_navigation'] = secondaryNavigation($artist_id);
    
	// Set the active landing page_title 
	$theme = new themer('explore/content');
	return $theme->make();
}
?>
