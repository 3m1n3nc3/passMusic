<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $framework, $databaseCL, $marxTime;  
	
	$PTMPL['site_url'] = $SETT['url']; 

	$PTMPL['skin'] = ' class="black-skin"'; 

	if (isset($_GET['view'])) {
		if ($_GET['view'] == 'about' || $_GET['view'] == 'contact') {

			// Show the empty banner
			$PTMPL['main_title'] = $PTMPL['main_content'] = 
			$PTMPL['features'] = $PTMPL['more_info'] = notAvailable($LANG['no_static_notice'], 'text-info', 2);

			if ($_GET['view'] == 'about') {
				// Prepare the about page

				$databaseCL->parent = 'about'; 
				$databaseCL->priority = 4;
				$first_content =  $databaseCL->fetchStatic(null, 1)[0]; 
				if ($first_content) {  
					$PTMPL['main_title'] = '<h3>'.$first_content['title'].'</h3>';
					$PTMPL['first_content'] = '<p>'.$first_content['content'].'</p>';
					$PTMPL['first_image'] = $first_content['banner'] ? '<img src="'.getImage($first_content['banner'], 1).'" class="img-fluid" alt="" style="max-height:400px;">' : ''; 
				}
 
				$databaseCL->priority = 3;
				$main_about =  $databaseCL->fetchStatic(null, 1)[0]; 
				if ($main_about) { 
					$PTMPL['page_title'] = 'About '.$configuration['site_name'];	
					// $PTMPL['seo_meta'] = seo_plugin(getImage($main_about['banner'], 1), $main_about['content'], $main_about['title']);

					$PTMPL['main_title'] = '<h3>'.$main_about['title'].'</h3>';
					$PTMPL['main_content'] = '<div class="text-center grey-text mb-5 mx-auto w-responsive lead">'.$main_about['content'].'</div>';
					$PTMPL['big_banner'] = $main_about['banner'] ? bigBanner($main_about['banner'], 1, $main_about['title'], $main_about['button_links']) : '<br>'; 
				}

 				$databaseCL->priority = '2';
				$features =  $databaseCL->fetchStatic(null, 1);  
				if ($features) {
					$feature = '';
					$i = 0;
					foreach ($features as $row) {
						$i++;
						$feature .= ' 
						<div class="icon-box">
							<div class="icon"><i class="fa '.$row['icon'].'"></i></div>
							<h4 class="title"><a href="">'.$row['title'].'</a></h4>
							<p class="description">'.$row['content'].'</p>
						</div>';
					}
					$PTMPL['features'] = $feature;
				}

 				$databaseCL->priority = '1';
				$more_info = $databaseCL->fetchStatic(null, 1);
				if ($more_info) {
					$info = '';
					$i = 0;
					foreach ($more_info as $row) {
						$i++;$eo = $i%2;
 
						$order_1 = $eo == 0 ? ' order-1 order-lg-2' : '';
						$order_2 = $eo == 0 ? ' order-2 order-lg-1' : '';
 
						$content = $framework->auto_template(str_ireplace('{$texp-&gt;', '{$texp->', $row['content']), 1); 
						$class = stripos($content, 'team-activator') ? 'team-section pb-4 ' : '';  
						$info .= ' 
							<div class="row about-extra">
								<div class="col-lg-6'.$order_1.'">
									<img src="'.getImage($row['banner'], 1).'" class="img-fluid" alt="" style="max-height:400px;">
								</div>
								<div class="col-lg-6 pt-4 pt-lg-0'.$order_2.'">
									<h4>'.$row['title'].'</h4>
									'.$content.'
								</div>
							</div>';
					}
					$PTMPL['more_info'] = $info;
				}

 				$databaseCL->priority = '5';
				$services = $databaseCL->fetchStatic(null, 1);
				if ($services) {
					$info = '';
					$i = 0;$ix = 11;
					foreach ($services as $row) {
						$i++; $ix++; $eo = $i%2;
 
						$offset_1 = $eo == 0 ? ' offset-lg-1' : ''; 
						$link = $framework->urlTitle($row['button_links'], 1);
						$color = $framework->mdbColors($ix);

						$content = $framework->auto_template(str_ireplace('{$texp-&gt;', '{$texp->', $row['content']), 1);  
 						$content = substr_replace($content, '<p class="description">', 0, 3);
						$class = stripos($content, 'team-activator') ? 'team-section pb-4 ' : '';  
						$info .= ' 
							<div class="col-md-6 col-lg-5'.$offset_1.' wow bounceInUp" data-wow-duration="1.4s">
								<div class="box">
									<div class="icon"><i class="fa '.$row['icon'].' '.$color.'"></i></div>
									<h4 class="title"><a href="'.$link.'">'.$row['title'].'</a></h4>
									 '.$content.' 
								</div>
							</div> ';
					}
					$PTMPL['services_info'] = $info;
				}
			} else {
				// Prepare the contacts page
				$databaseCL->parent = 'contact'; 
				$databaseCL->priority = '3';
				$intro =  $databaseCL->fetchStatic(null, 1)[0];  

				$PTMPL['page_title'] = 'Contact '.$configuration['site_name'];	
				// $PTMPL['seo_meta'] = seo_plugin(getImage($intro['banner'], 1), $intro['content'], $intro['title']);

				$PTMPL['big_banner'] = $intro['banner'] ? bigBanner($intro['banner'], 1, $intro['title'], $intro['button_links']) : '<br>'; 

				$PTMPL['main_title'] = $intro['title'];
				$PTMPL['main_content'] = $intro['content'];
				$PTMPL['banner'] = $intro['banner'] ? banner($intro['banner']) : '';
				$PTMPL['main_address'] = $configuration['site_office'];
				$PTMPL['main_email'] = $configuration['email'];
				$PTMPL['main_phone'] = $configuration['site_phone'];
				$PTMPL['map_embed_url'] = $configuration['map_embed_url']; 
			}
			$theme = new themer('static/'.$_GET['view']);
			$PTMPL['page_content'] = $theme->make();
		} else { 
			$more_info = $databaseCL->fetchStatic($_GET['view'])[0];   
			if ($more_info) {

				$PTMPL['page_title'] = $more_info['title'];	
				// $PTMPL['seo_meta'] = seo_plugin(getImage($more_info['banner'], 1), $more_info['content'], $more_info['title']); 
				// 
				$PTMPL['more_info'] = '  
					<section id="info-'.$more_info['id'].'" class="section wow fadeIn" data-wow-delay="0.3s"> 
						<h1 class="font-weight-bold text-center h1">'.$more_info['title'].'</h1> 
						<div class="text-center grey-text mb-5 mx-auto w-responsive">'.$more_info['content'].'</div> 
					</section>'; 

				$PTMPL['big_banner'] = $more_info['banner'] ? bigBanner($more_info['banner'], 1, $more_info['title'], $more_info['button_links']) : '<br>'; 
			} else {
				$PTMPL['more_info'] = '<div class="m-5">'.notAvailable('', '', 403).'</div>';
			}
				
			$theme = new themer('static/main');

			$PTMPL['page_content'] = $theme->make();
		}
	} 
 
	// $PTMPL['page_sidebar'] = site_sidebar();

	// Set the active landing page_title 
	$theme = new themer('static/container');
	return $theme->make();
}
?> 
