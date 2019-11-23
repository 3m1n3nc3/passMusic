<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $configuration, $framework, $databaseCL; 

	$PTMPL['page_title'] = 'Search '.$configuration['site_name']; 

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

        // $framework->limit_records = 1;
        $framework->all_rows = $databaseCL->searchEngine($_GET['q']);
        $PTMPL['pagination'] = $framework->pagination(); 
        $search_results = $databaseCL->searchEngine($_GET['q']);
    } else {
        $search_cards .= '<div class="section-title text-center">Featured Content</div>';
        $framework->limit_records = 8;
        $databaseCL->filters = true;
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
    }
     
	$theme = new themer('search/search');
	return $theme->make();
}

// Add two files to uploads dir; 
// 1: playlist.png
// 2: music.png
?>
