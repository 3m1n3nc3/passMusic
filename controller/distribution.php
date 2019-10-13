<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$msg = null; $t = 3; $errors = [];
	$release_ids = isset($_GET['rel_id']) ? $_GET['rel_id'] : null;
	if ($release_ids) {
		$get_release = $databaseCL->fetchReleases(1, $_GET['rel_id'])[0];
	}

	// Set local links
	$PTMPL['artists_services'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=artists-services');

	$rel = isset($_GET['rel']) ? $_GET['rel'] : null;

	$st = 0;
	if (isset($_POST['sort_releases'])) {
	 	$_POST['sort'] = $_POST['sort'];
	} elseif (isset($_GET['stat'])) {
		$_POST['sort'] = $_GET['stat'];
		$_POST['sort_releases'] = 1;
	}
	if (isset($_POST['sort_releases'])) {
		unset($_GET['stat']);
		if ($_POST['sort'] == 3) {
			$sort = 'approved';
			$PTMPL['s_3'] = ' active';
		} elseif ($_POST['sort'] == 2) {
			$sort = 'pending approval';
			$PTMPL['s_2'] = ' active';
			$st = 1;
		} elseif ($_POST['sort'] == 0) {
			$sort = 'removed';
			$PTMPL['s_0'] = ' active';
		} else {
			$sort = 'incomplete';		
			$PTMPL['s_1'] = ' active';
		}
		$status = $_POST['sort']; 
	} else {
		$sort = 'incomplete';
		$PTMPL['s_1'] = ' active';
		$status = 1; 
	}

    $databaseCL->status = $status; 
	$list_rel = $databaseCL->fetchReleases();
	if ($list_rel) {
		$list_releases = '';
		foreach ($list_rel as $key => $_rel) {
			$list_releases .= releasesCard($_rel['release_id']);
		} 
		$PTMPL['list_releases'] = $list_releases; 
	} else {
		if ($st) {
			$notice = sprintf($LANG['no_releases_var'], $sort);
		} else {
			$notice = sprintf($LANG['no_releases'], $sort);
		}
		$PTMPL['list_releases'] = notAvailable($notice, 'display-4');
	} 

	$PTMPL['manage_artist_url'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=artist-services&rel=manage');
 
	// Set the active landing page_title 
	if (isset($get_release) && $get_release['by'] == $user['uid']) {
		$release_id = $get_release['release_id'];
		$release_artist = $databaseCL->fetchRelease_Artists(1, $release_id)[0]; 
		$release_audio = $databaseCL->fetchRelease_Audio(null, $release_id);
		$release_audio_count = $release_audio ? count($release_audio) : 0;
		$rau_string = $release_artist ? '&artist='.$release_artist['username'] : '';

		$PTMPL['release_track_list'] = releasesTracklist($release_id);
		$PTMPL['release_audio_url'] = $SETT['url'].'/connection/uploader.php?release='.$release_id.'&rel=audio';
		$PTMPL['release_artwork_url'] = $SETT['url'].'/connection/uploader.php?release='.$release_id.'&rel=artwork'.$rau_string;

		$PTMPL['release_details'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&set=details&rel_id='.$release_id);
		$PTMPL['release_audio'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&set=audio&rel_id='.$release_id);
		$PTMPL['release_artwork_update'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&set=artwork&rel_id='.$release_id);

		$PTMPL['release_home'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$release_id);
		// Release details
		$PTMPL['release_artist'] = $release_artist['name'];
		$PTMPL['release_artwork'] = getImage($get_release['art'], 1);
		$PTMPL['release_upc'] = $get_release['upc'];
		$PTMPL['release_title'] = isset($_POST['title']) ? $_POST['title'] : $get_release['title'];
		$PTMPL['release_genre'] = $get_release['p_genre'];
		$PTMPL['release_label'] = isset($_POST['label']) ? $_POST['label'] : $get_release['label'];
		$PTMPL['release_description'] = isset($_POST['description']) ? $_POST['description'] : $get_release['description'];
		$PTMPL['predefined_artist'] = $framework->autoComplete();

 		$PTMPL['tag_list'] = $framework->autoComplete(1);
		$release_tags = isset($_POST['tags']) ? $_POST['tags'] : $get_release['tags'];
		$PTMPL['preset_tags'] = $framework->autoComplete(2, $release_tags);

		$PTMPL['release_cline'] = isset($_POST['c_line']) ? $_POST['c_line'] : $get_release['c_line'];
		$PTMPL['release_pline'] = isset($_POST['p_line']) ? $_POST['p_line'] : $get_release['p_line'];
		$PTMPL['release_cline_year'] = $get_release['c_line_year'];
		$PTMPL['release_pline_year'] = $get_release['p_line_year'];
		$PTMPL['release_cline_year_alt'] = isset($_POST['c_line_year']) ? $_POST['c_line_year'] : ($get_release['c_line_year'] ? $get_release['c_line_year'] : date('Y'));
		$PTMPL['release_pline_year_alt'] = isset($_POST['p_line_year']) ? $_POST['p_line_year'] : ($get_release['p_line_year'] ? $get_release['p_line_year'] : date('Y'));

		$PTMPL['release_date'] = isset($_POST['release_date']) ? $_POST['release_date'] : $get_release['release_date'];
		$PTMPL['release_approved'] = $get_release['approved_date'] ? $get_release['approved_date'] : 'Pending';

		// Steps completion
		$step_1 = $get_release['title'] != '' ? $marxTime->percenter(10, 100) : 0;
		$step_2 = $get_release['c_line'] != '' && $get_release['p_line'] != '' && $get_release['label'] != '' && $get_release['release_date'] != '' ? $marxTime->percenter(25, 100) : 0;
		$step_3 = $release_audio_count > 0 ? $marxTime->percenter(25, 100) : 0;
		$step_4 = $get_release['art'] != '' ? $marxTime->percenter(25, 100) : 0;
		$step_5 = $release_artist != '' ? $marxTime->percenter(15, 100) : 0;

		$PTMPL['release_explicit'] 			= $get_release['explicit'] ? 'Explicit' : 'Not Explicit';
		$PTMPL['explicit_badge'] 			= $get_release['explicit'] ? 'badge-danger' : 'badge-success';
		$PTMPL['release_artwork_status'] 	= $step_4 ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; 
		$PTMPL['release_audio_status'] 		= $step_3 ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
		$PTMPL['release_details_status'] 	= $step_2 ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
		$PTMPL['release_progress'] = $progress = $step_1 + $step_2 + $step_3 + $step_4 + $step_5;
    	
    	$PTMPL['missing_artist'] = !$release_artist && $progress == 60 ? messageNotice($LANG['missing_artist'], 2, 6, 1) : '';

		// Set role form options fields
		$roles = array('primary', 'composer', 'featuring', 'producer', 'with', 'performer', 'dj', 'remixer', 'conductor', 'lyricist', 'arranger', 'orchestra', 'actor');

		// Fetch genre form options fields
		$genres = $databaseCL->fetchGenre();
		$PTMPL['genres_option'] = $PTMPL['genres_option_2'] = $genres_option = $genres_option_2 = '';
		foreach ($genres as $key => $value) { 
			$sel = $get_release['p_genre'] == $value['name'] ? ' selected="selected"' : '';
			$sel2 = $get_release['s_genre'] == $value['name'] ? ' selected="selected"' : '';
			$PTMPL['genres_option'] = $genres_option .= '<option value="'.$value['name'].'"'.$sel.'>'.$value['title'].'</option>';
			$PTMPL['genres_option_2'] = $genres_option_2 .= '<option value="'.$value['name'].'"'.$sel2.'>'.$value['title'].'</option>';
		}

		if ($progress == 100) {
    		$publ_url = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&set=publish&rel_id='.$get_release['release_id']);
			$PTMPL['complete_btn'] = $get_release['status'] == 1 ? '
			<div class="d-flex m-2">
				<a href="'.$publ_url.'" class="btn btn-success flex-grow-1 mr-2 font-weight-bolder">Publish Release</a>  
			</div>' : '';
		}
 
		if (isset($_GET['action'])) {
			if ($_GET['action'] == 'manage') {
				if (isset($_GET['modify'])) {
					if ($_GET['modify'] == 'remove') {
						// Remove this release from sales, without deleting files
						if ($get_release['status'] != 10) {echo $get_release['status'];
							echo $do = $databaseCL->salesAction($release_id, 2);
							if ($do == 1) {
								$msg = $LANG['release_removed'];
								$t = 1;
							}
						}
					}
					$PTMPL['notification'] = messageNotice($msg, $t); 
				}
				if (isset($_GET['set']) && $_GET['set'] !== 'publish') {
					if ($get_release['status'] == 2) {
						$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$release_id), 1);
					}
					if ($_GET['set'] == 'details') {
						$artists = $databaseCL->fetchRelease_Artists(null, $release_id);
						if ($artists) {
							$PTMPL['add_more_artists'] = $add_more_artists = '';
							foreach ($artists as $key => $value) {
								$roles_option = '';
								foreach ($roles as $role) {
									$active = $value['role'] == $role ? ' selected="selected"' : '';
									$disabled = $role == 'primary' ? ' disabled="disabled"' : '';
									$roles_option .= '<option value="'.$role.'"'.$active.$disabled.'>'.ucfirst($role).'</option>';
								}
								$PTMPL['add_more_artists'] = $add_more_artists .= '
								<div class="col-8">
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<span class="input-group-text">Artist Name</span>
										</div>
										<input type="hidden" name="username[]" value="'.$value['username'].'">
										<input type="text" name="artist[]" class="form-control" value="'.$value['name'].'">
									</div> 
								</div>
								<div class="col">
									<select name="artist_role[]" class="form-control"> 
										'.$roles_option.'
									</select>
								</div>';
							}
							$PTMPL['primary_username'] = $release_artist ? '<input type="hidden" name="username[]" value="'.$release_artist['username'].'">' : '';
						} 
						if (isset($_POST['submit_details'])) {
							$artist_count = count($_POST['artist']);
							if ($artist_count > 0) {
								$i = 0;
								foreach ($_POST['artist'] as $key => $name) {
									if ($name != '') {  
										$username = isset($_POST['username'][$key]) ? $_POST['username'][$key] : '';
										$artist_role = isset($_POST['artist_role'][$key]) ? $_POST['artist_role'][$key] : '';

										$new_username = $framework->generateUserName($databaseCL->db_prepare_input($name), 1);

										if (isset($_POST['username'][$key])) { 
											// Check if this user exist then save the users data instead of post data
											$userdata = $databaseCL->userData($username, 2);
											if ($userdata) { 
												$name = $userdata['fname'].' '.$userdata['lname'];
											}
											$sql = sprintf("UPDATE new_release_artists SET `name` = '%s', `role` = '%s' WHERE `username` = '%s'", $databaseCL->db_prepare_input($name), $databaseCL->db_prepare_input($artist_role), $databaseCL->db_prepare_input($username));
										} else { 
											// Check if this user exist then save the users data instead of post data
											$new_userdata = $databaseCL->userData($new_username, 2);
											if ($new_userdata) { 
												$name = $new_userdata['fname'].' '.$new_userdata['lname'];
											}
											$sql = sprintf("INSERT INTO new_release_artists (`name`, `role`, `release_id`, `username`, `by`) VALUES ('%s', '%s', '%s', '%s', '%s')", $databaseCL->db_prepare_input($name), $databaseCL->db_prepare_input($artist_role), $databaseCL->db_prepare_input($_GET['rel_id']), $new_username, $user['uid']);
										}
										$databaseCL->dbProcessor($sql, 0, 1);
									}
								}
							} else {
								echo 1;
							}
							$release_id = $databaseCL->db_prepare_input($_GET['rel_id']);
							$title = $databaseCL->db_prepare_input($_POST['title']);
							$tags = $databaseCL->db_prepare_input($_POST['tags']);
							$desc = $databaseCL->db_prepare_input($_POST['description']);
							$label = $databaseCL->db_prepare_input($_POST['label']);
							$explicit = $databaseCL->db_prepare_input($_POST['explicit']);
							$p_genre = $databaseCL->db_prepare_input($_POST['p_genre']);
							$s_genre = $databaseCL->db_prepare_input($_POST['s_genre']);
							$p_line = $databaseCL->db_prepare_input($_POST['p_line']);
							$c_line = $databaseCL->db_prepare_input($_POST['c_line']);
							$p_line_year = $databaseCL->db_prepare_input($_POST['p_line_year']);
							$c_line_year = $databaseCL->db_prepare_input($_POST['c_line_year']);
							$explicit = $databaseCL->db_prepare_input($_POST['explicit']);
							$release_date = $databaseCL->db_prepare_input($_POST['release_date']);

							if ($title == '') {
								$msg = $LANG['enter_title'];
							} elseif ($_POST['artist'][0] == '') {
								$msg = $LANG['enter_primary_artist'];
							} elseif ($c_line == '') {
								$msg = $LANG['enter_composition_copyright'];
							} elseif ($p_line == '') {
								$msg = $LANG['enter_recording_copyright'];
							} elseif ($label == '') {
								$msg = $LANG['enter_record_label'];
							} elseif ($release_date == '') {
								$msg = $LANG['enter_release_date'];
							} else {
								$sql = sprintf("UPDATE new_release SET `title` = '%s', `tags` = '%s', `description` = '%s', `p_genre` = '%s', `s_genre` = '%s', `p_line` = '%s', `c_line` = '%s', `p_line_year` = '%s', `c_line_year` = '%s', `label` = '%s', `release_date` = '%s', `explicit` = '%s' WHERE `release_id` = '%s'", $title, $tags, $desc, $p_genre, $s_genre, $p_line, $c_line, $p_line_year, $c_line_year, $label, $release_date, $explicit, $release_id);
								$databaseCL->dbProcessor($sql, 0, 1);
								$msg = $LANG['information_saved'];$t = 1;
							} 
							$PTMPL['notification'] = messageNotice($msg, $t); 
						}
						$theme = new themer('distribution/new_release_details'); 
					} elseif ($_GET['set'] == 'audio') {
            			$release_audio = $databaseCL->fetchRelease_Audio(null, $release_id);
            			if ($release_audio) {
            				$PTMPL['onclick_delete'] = ' onclick="removeAllAudio('.$release_id.')"'; 
            			}
						$theme = new themer('distribution/new_release_audio'); 
					} elseif ($_GET['set'] == 'artwork') {
						$theme = new themer('distribution/new_release_artwork'); 
					}
				} else {  
					$release_id = $databaseCL->db_prepare_input($_GET['rel_id']);
					if (isset($_GET['set']) && $_GET['set'] == 'publish') {
						if ($get_release['status'] != 1) {
							$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$release_id), 1);
						}
						$publisher = new themer('distribution/new_release_publish'); 
						$PTMPL['publisher'] = $publisher->make();
					}
					if (isset($_POST['free_distribution'])) {
						$sql = sprintf("UPDATE new_release SET `status` = '2' WHERE `release_id` = '%s'", $release_id);
						$set = $databaseCL->dbProcessor($sql, 0, 2);
						if ($set == 1) {
							$PTMPL['publisher'] = bigNotice($LANG['release_submited'], 1, 'bg-white shadow');
						} elseif ($set == 0) {
							$PTMPL['publisher'] = bigNotice($LANG['release_already_submited'], 2, 'bg-white shadow');
						} else {
							$PTMPL['publisher'] = $set;
						}
					} elseif (isset($_POST['premium_distribution'])) {
						# code...
					}
					$theme = new themer('distribution/new_release_home'); 
				}
			}
		}
	} else {
		if (isset($_GET['action'])) {
			if ($_GET['action'] == 'new_release') {
				// Create a new release or a featured playlist
				// 
				if (isset($_POST['create_release']) || isset($_POST['create_playlist'])) {
					$post_what = isset($_POST['create_release']) ? 'release' : 'playlist';
					$title = $databaseCL->db_prepare_input($_POST['title']);
					$pl = $databaseCL->fetchPlaylist($title)[0];
					if ($title == '') {
						$msg = 'Please enter a title for this release';
					} elseif ($post_what == 'playlist' && $pl) {
						$msg = 'You already have a '.$post_what.' named "'.$title.'"';
						$t = 3;
					} else {
						$release_id = $framework->token_generator(13);
						if ($post_what == 'release') {
							$sql = sprintf("INSERT INTO new_release (`release_id`, `title`, `upc`, `by`) VALUES ('%s', '%s', '%s', '%s')", $release_id, $title, $databaseCL->db_prepare_input($_POST['upc']), $user['uid']);
						} elseif ($post_what == 'playlist') {
							$sql = sprintf("INSERT INTO playlist (`by`, `title`, `public`, `plid`, `featured`) 
								VALUES ('%s', '%s', '%s', '%s', '%s')", $user['uid'], $title, 0, $release_id, 1);
							$msg = sprintf($LANG['new_featured_playlist_created'], $post_what);
							$t = 1; 
						}
						$set = $databaseCL->dbProcessor($sql, 0, 1);
						if ($post_what == 'release') {
							$msg = 'Your '.$post_what.' has been created';
							$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$release_id), 1);
						}
					}
					if (isset($set)) {
						$msg = $set == 1 ? $msg : $set;
					}
					$PTMPL['notification'] = bigNotice($msg, $t); 
				}
				$theme = new themer('distribution/new_release'); 
			} elseif ($_GET['action'] == 'releases') {
				// Show a list of all your releases and manage them effectively
				// 
				$theme = new themer('distribution/all_releases');
			} elseif ($_GET['action'] == 'artist-services') {
				// Services related to artist and label management
				// 
				if ($rel == 'manage') {
					$rel_artists = $databaseCL->fetchRelease_Artists(2, $user['uid']);
					if (isset($_GET['artist'])) {
						$get_artist = $databaseCL->fetchRelease_Artists(3, $_GET['artist'])[0]; 
						// Show the form to update an artist
						// 
						if ($get_artist) {
							$userdata = $framework->userData($get_artist['username'], 1);
							if ($userdata) {
								$PTMPL['photo'] = $photo = getImage($userdata['photo'], 1);
								$PTMPL['fname'] = $fname = $userdata['fname'];
								$PTMPL['lname'] = $lname = $userdata['lname'];
								$PTMPL['description'] = $description = $userdata['intro'];
								$PTMPL['this_user'] = $name = $fname.' '.$lname;
								$PTMPL['profile_link'] = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$userdata['username']);
							} else {
								$PTMPL['photo'] = $photo = getImage($get_artist['photo'], 1);
								$PTMPL['description'] = $description = $get_artist['intro'];
								$PTMPL['this_user'] = $PTMPL['fname'] = $name = $get_artist['name'];
								$named = explode(' ', $get_artist['name']);
								if (count($named)>1) {
									$PTMPL['fname'] = $fname = $named[0];
									$PTMPL['lname'] = $lname = $named[1];
								}
							}

							if (isset($_POST['update_profile'])) {
								$PTMPL['fname'] = $fname = $framework->db_prepare_input($_POST['fname']);
								$PTMPL['lname'] = $lname = $framework->db_prepare_input($_POST['lname']);
								$PTMPL['description'] = $description = $framework->db_prepare_input($_POST['description']);
								if ($fname == '') {
									$errors[] .= $LANG['no_fname'];
								}
								if ($lname == '') {
									$errors[] .= $LANG['no_lname'];
								}

								if (empty($errors)) { 
									$name = $fname.' '.$lname;
									$sql = sprintf("UPDATE new_release_artists SET `name` = '%s', `intro` = '%s' WHERE `username` = '%s'", $name, $description, $get_artist['username']);
									$update = $databaseCL->dbProcessor($sql, 0, 2);

									if ($userdata) {
										$sql = sprintf("UPDATE users SET `fname` = '%s', `lname` = '%s', `intro` = '%s' WHERE `username` = '%s'", $fname, $lname, $description, $get_artist['username']);
										$update = $databaseCL->dbProcessor($sql, 0, 2);
									}
	 
									if ($update === 1 || $update === 0 ) {
										$PTMPL['notification'] = messageNotice($LANG['information_saved'], 1, 'bg-white shadow');
									} else {
										$PTMPL['notification'] = $update;
									}
								} else {
									$PTMPL['notification'] = messageNotice($errors[0], 3, 'bg-white shadow');;
								}
							}
							$a_id = $get_artist['username'];
							if (!allowAccess($get_artist['by'])) {
								$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&notice=true&response=403'), 1);
							}
							$rau_string = '&artist='.$get_artist['username'];
							$PTMPL['profile_photo_url'] = $SETT['url'].'/connection/uploader.php?release=profile&rel=artwork'.$rau_string;
							$theme = new themer('distribution/artist_update'); 
						} else {
							$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&notice=error&response=403'), 1);
						}
					} else {
						// Show the list of all available artists
						// 
						if ($rel_artists) { 
							$list_artists = '';
							foreach ($rel_artists as $ra => $artist) {
								$userdata = $framework->userData($artist['username'], 1);
								$artist_data = $databaseCL->fetchRelease_Artists(3, $artist['username'])[0];
								if ($userdata) {
    								$profile_link = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$userdata['username']);
									$profile = '<a href="'.$profile_link.'" class="btn btn-primary">See Profile</a>';
									$photo = getImage($userdata['photo'], 1);
									$name = $userdata['fname'].' '.$userdata['lname'];
									$username = $artist['username'];
								} else {
									$profile = '';
									$photo = getImage($artist_data['photo'], 1);
									$name = $artist_data['name'];
									$username = $artist_data['username'];
								}
								$conf = 'Are you sure you want to delete this artist? This is irreversible and will remove every record of this user from '.$PTMPL['site_title'].', like they were never here!';
    							$update = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=artist-services&rel=manage&artist='.$artist_data['id']);
								$list_artists .=  
								'<div class="col-lg-3 col-md-4 col-sm-6 col-sm-2 mb-3" id="artist_'.$username.'">
									<div class="card">
										<a 
											onclick="deleteItem({type: 2, conf_: \''.$conf.'\', action: \'artist\', id: \''.$username.'\'})">
											<i class="fa fa-2x fa-trash m-2 text-danger pc-hover-1 pointer" style="position: absolute; right: 0;"></i>
										</a>
										<img class="card-img-top" src="'.$photo.'" alt="'.$name.'" width="200px" height="200px;">
										<div class="card-body text-center" style="padding: 5px;">
											<h4 class="card-title">'.$name.'</h4>
											<a href="'.$update.'" class="btn btn-primary">Update Profile</a>
											'.$profile.'
										</div>
									</div>
								</div>';
							}
							$PTMPL['list_artists'] = $list_artists;
						} else {		
							$PTMPL['list_artists'] =  '<div class="col-12">'.notAvailable('You have no Artists on roll!', 'display-4').'</div>';
						}
						$theme = new themer('distribution/artist_lists'); 
					}
				} else {
					$theme = new themer('distribution/artist_services');
				}
			} elseif ($_GET['action'] == 'sales-report') {
				$stat = $databaseCL->releaseStats($user['uid'])[0];
				$PTMPL['quarterly_data'] = dataSet();
				$PTMPL['most_viewed_data'] = dataSet(1);
				$PTMPL['tracks_barchart'] = dataSet(2);

				$PTMPL['total_releases'] = $stat['total'];
				$PTMPL['approved_releases'] = $stat['approved'];
				$PTMPL['pending_releases'] = $stat['pending'];
				$PTMPL['incomplete_releases'] = $stat['incomplete'];
				$PTMPL['total_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases&stat=1');
				$PTMPL['approved_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases&stat=3');
				$PTMPL['pending_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases&stat=2');
				$PTMPL['incomplete_link'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases&stat=1');

				$theme = new themer('distribution/sales_reports');
			} else {
				// Show the default landing page for the distribution section
				// 
				$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&notice=error&response=403'), 1);
			}
		} else {
			// Show the default landing page for the distribution section
			// 
			$theme = new themer('distribution/content');
		}
	}
	return $theme->make();
}
?>
