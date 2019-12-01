<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url'];

	// Banner section
	$databaseCL->parent = 'home'; 
	$databaseCL->priority = 5;
	$home_banner =  $databaseCL->fetchStatic(null, 1)[0];
	if ($home_banner) {
		$PTMPL['homepage_banner'] = '					
		<h1 class="text-uppercase">'.$home_banner['title'].'</h1>
		<p class="text-white">'.$framework->rip_tags($home_banner['content']).'</p>'. $framework->generateButton($home_banner['button_links'], 1);
		$PTMPL['banner_background_image'] = $home_banner['banner'] ? getImage($home_banner['banner'], 1) : ''; 
	}

	// Music Buzz section
	$databaseCL->parent = 'home'; 
	$databaseCL->priority = 4;
	$home_buzz =  $databaseCL->fetchStatic(null, 1)[0]; 
	if ($home_buzz) {  
		$PTMPL['buzz_title'] = $marxTime->retitle($home_buzz['title']);
		$PTMPL['buzz_content'] = str_ireplace('<p>', '<p class="card-text align-self-center my-4 text-white">', $home_buzz['content']);
		$PTMPL['buzz_button'] = $framework->generateButton($home_buzz['button_links'], 1);
		$PTMPL['buzz_image'] = $home_buzz['banner'] ? getImage($home_buzz['banner'], 1) : ''; 
	}

	// Why choose us section
	$databaseCL->parent = 'home'; 
	$databaseCL->priority = 3;
	$home_choose = $databaseCL->fetchStatic(null, 1); 
	if (!$home_choose) {
		$databaseCL->parent = 'about'; 
		$databaseCL->priority = 2;
		$home_choose =  $databaseCL->fetchStatic(null, 1); 
	}
	if ($home_choose) {
		$choice_info ='';
		foreach ($home_choose as $choose) {
			$choice_info .= '
			<div class="col-lg-3">
				<div class="agileits-services-grids mt-lg-0 mt-3">
					<div class="services-top">
						<span class="fa '.icon(3, $choose['icon']).'"></span>
						<h4> '.$choose['title'].' </h4>
					</div>
					<p>'.$framework->rip_tags($choose['content']).'</p>
				</div>
			</div>';
		}
		$PTMPL['home_choose'] = $choice_info;
	}

	// WHY CHOOSE section
	$databaseCL->parent = 'home'; 
	$databaseCL->priority = 2;
	$home_why =  $databaseCL->fetchStatic(null, 1)[0]; 
	if ($home_why) {  
		$PTMPL['why_title'] = $home_why['title'];
		$PTMPL['why_content'] = $framework->rip_tags($home_why['content']);
		$PTMPL['why_button'] = $framework->generateButton($home_why['button_links'], 1);
		$PTMPL['why_image'] = $home_why['banner'] ? getImage($home_why['banner'], 1) : ''; 
	}

	// Addicted Section
	$databaseCL->parent = 'home'; 
	$databaseCL->priority = 1;
	$home_adicted =  $databaseCL->fetchStatic(null, 1)[0]; 
	if ($home_adicted) {  
		$PTMPL['adicted_title'] = $marxTime->retitle($home_adicted['title']);
		$PTMPL['adicted_content'] = str_ireplace('<p>', '<p class="card-text align-self-center my-sm-4 my-3 text-white">', $home_adicted['content']);
		$PTMPL['adicted_button'] = $framework->generateButton($home_adicted['button_links'], 1, 1); 
	}

	// Contact top Section
	$databaseCL->parent = 'home'; 
	$databaseCL->priority = 0;
	$contact_top =  $databaseCL->fetchStatic(null, 1)[0]; 
	if ($contact_top) {  
		$PTMPL['contact_top_title'] = $contact_top['title'];
		$PTMPL['contact_top_content'] = str_ireplace('<p>', '<p class="text-white w-75 mx-auto">', $contact_top['content']);
		$PTMPL['contact_top_middle'] = $contact_top['button_links']; 
		$PTMPL['contact_top_image'] = $contact_top['banner'] ? getImage($contact_top['banner'], 1) : ''; 
	}

	// Fetch the contact social data
	$PTMPL['fetch_social'] = fetchSocialInfo($configuration);

	// Fetch the featured tracks
	$databaseCL->filters = true;
	$fetch_featured = $databaseCL->searchEngine();
	if ($fetch_featured) {
		$featured_list = '';
		foreach ($fetch_featured as $featured) {
			if ($featured['type'] == 2) {
				$link = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$featured['safe_link']);
				$featured_list .= '
				<div class="col-md-6 my-md-4">
					<a href="'.$link.'">
						<img src="'.getImage($featured['art'], 1).'" class="img-fluid" alt="" />
						<h5 class="blog-title card-title">'.$featured['title'].'</h5>
						<p>'.$framework->myTruncate($featured['description'], 130).'</p>
					</a>
				</div>';
			}
		}
		$PTMPL['featured_list'] = $featured_list;
	}

	$PTMPL['seo_meta_plugin'] = seo_plugin($home_banner['banner']);

	if (isset($_GET['logout'])) {
		if ($_GET['logout'] == 'user') {
			$framework->sign_out(1);
		} elseif ($_GET['logout'] == 'admin') {
			$framework->sign_out(1, 1);
		}
		$framework->redirect(cleanUrls($SETT['url'].'/index.php?page=explore'), 1);
	}

	// Set the active landing page_title 
	$theme = new themer('homepage/content');
	return $theme->make();
}
?>
