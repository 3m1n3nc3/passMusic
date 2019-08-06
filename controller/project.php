<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $framework; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	// Set the active landing page_title 
	$theme = new themer('project/content');
	return $theme->make();
}
?>
