<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $configuration, $framework, $databaseCL; 

	$PTMPL['page_title'] = $LANG['search'].' '.$configuration['site_name']; 

	$artist_id = isset($_GET['artist']) ? $_GET['artist'] : (isset($user) ? $user['uid'] : '');
    $artist = $framework->userData($artist_id, 1);  

    $PTMPL['secondary_navigation'] = secondaryNavigation($artist_id);

    // Fetch suggested users 
    $PTMPL['sidebar_suggested_users'] = sidebar_userSuggestions($artist_id);

    $search_cards = '';
    // if (isset($_POST['search_input'])) {
    //     $q = $framework->urlRequery('&q='.$_POST['search_input']);
    //     $framework->redirect($q, 1); 
    // }
    if (isset($_GET['q']) && $_GET['q'] !== '' && strlen($_GET['q']) >= 3) {
        $PTMPL['search_input'] = $_GET['q']; 
        $search_query = $_GET['q'];

        // Check if this search is a hashtag
        $new_query = str_ireplace('#', '', $_GET['q'], $hashtag);
        if ($hashtag) {
            $databaseCL->tags = true;
            $search_query = $new_query;
        }
        // $framework->limit_records = 1;
        $framework->all_rows = $databaseCL->searchEngine($search_query);
        $PTMPL['pagination'] = $framework->pagination(); 
        $search_results = $databaseCL->searchEngine($search_query);
        $is_search = 1;
    } elseif (isset($_GET['rel']) && $_GET['rel'] == 'find') {
        $PTMPL['page_title'] =  'Who to follow';
        $databaseCL->limit = 12;
        $databaseCL->filters = $_GET['rel']; 
        $search_results = $databaseCL->searchEngine();
    } else {
        // $PTMPL['page_title'] =  'Featured Content';
        $framework->limit_records = 12;
        $databaseCL->filters = 'featured';
        $framework->all_rows = $databaseCL->searchEngine();
        $PTMPL['pagination'] = $framework->pagination(); 
        $search_results = $databaseCL->searchEngine();
    }
    if ($search_results) {
        if ($search_results) {
            foreach ($search_results as $rows) {
                $search_cards .= printSearch($rows);  
            }
        }
        $PTMPL['search_cards'] = $search_cards; 
    } elseif (isset($is_search)) {
        $PTMPL['search_cards'] = notAvailable('No search results for "'.$_GET['q'].'"', 'h1');
    }

    // Set the seo tags
    $PTMPL['seo_meta_plugin'] = seo_plugin(null, $PTMPL['page_title']);
     
	$theme = new themer('search/search');
	return $theme->make();
}

// Add two files to uploads dir; 
// 1: playlist.png
// 2: music.png
?>
