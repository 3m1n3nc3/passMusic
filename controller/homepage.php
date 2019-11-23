<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $framework; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	if (isset($_GET['logout'])) {
		if ($_GET['logout'] == 'user') {
			$framework->sign_out(1);
		} elseif ($_GET['logout'] == 'admin') {
			$framework->sign_out(1, 1);
		}
		// $framework->redirect(cleanUrls($SETT['url'].'/index.php?page=introduction'), 1);
	}

	// Set the active landing page_title 
	$theme = new themer('homepage/content');
	return $theme->make();
}
?>
