<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $framework; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$project_id = isset($_GET['project']) ? $_GET['project'] : (isset($_GET['id']) ? $_GET['id'] : '');
    $project = $framework->userData($artist_id, 1);

	// Set the active landing page_title 
	$theme = new themer('project/content');
	return $theme->make();
}
// Project tables: project,collaborators,instrumentals,stems,project_followers
?>
