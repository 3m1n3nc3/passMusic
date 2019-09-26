<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$msg = null; $t = 3;
	$release_ids = isset($_GET['rel_id']) ? $_GET['rel_id'] : null;
	if ($release_ids) {
		$get_release = $databaseCL->fetchReleases(1, $_GET['rel_id'])[0];
	}
	if (isset($_POST['sort_releases'])) {
		if ($_POST['sort'] == 3) {
			$sort = 'approved';
			$PTMPL['s_3'] = ' active';
		} elseif ($_POST['sort'] == 2) {
			$sort = 'pending approval';
			$PTMPL['s_2'] = ' active';
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
		$PTMPL['list_releases'] = notAvailable('No releases '.$sort, 'display-4', 1);
	} 

	// Set the active landing page_title 
	if (isset($get_release)) {
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

		// Set role form options fields
		$roles = array('primary', 'composer', 'featuring', 'producer', 'with', 'performer', 'dj', 'remixer', 'conductor', 'lyricist', 'arranger', 'orchestra', 'actor');

		// Fetch genre form options fields
		$genres = $databaseCL->fetchGenre();
		$PTMPL['genres_option'] = $genres_option = $PTMPL['genres_option_2'] = $genres_option_2 = '';
		foreach ($genres as $key => $value) { 
			$sel = $get_release['p_genre'] == $value['name'] ? ' selected="selected"' : '';
			$sel2 = $get_release['s_genre'] == $value['name'] ? ' selected="selected"' : '';
			$PTMPL['genres_option'] = $genres_option .= '<option value="'.$value['name'].'"'.$sel.'>'.$value['title'].'</option>';
			$PTMPL['genres_option_2'] = $genres_option_2 .= '<option value="'.$value['name'].'"'.$sel2.'>'.$value['title'].'</option>';
		}

		if ($progress == 100) {
    		$publ_url = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&set=publish&rel_id='.$get_release['release_id']);
			$PTMPL['complete_btn'] = '
			<div class="d-flex m-2">
				<a href="'.$publ_url.'" class="btn btn-success flex-grow-1 mr-2 font-weight-bolder">Publish Release</a>  
			</div>';
		}
 
		if (isset($_GET['action'])) {
			if ($_GET['action'] == 'manage') {
				if (isset($_GET['set']) && $_GET['set'] !== 'publish') {
					if ($get_release['status'] == 2) {
						$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$release_id.''), 1);
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
								foreach ($_POST['artist'] as $key => $name) {
									if ($name != '') {
										if (isset($_POST['username'][$key])) {
											$sql = sprintf("UPDATE new_release_artists SET `name` = '%s', `role` = '%s', `release_id` = '%s', `username` = '%s' WHERE `username` = '%s'", $databaseCL->db_prepare_input($name), $databaseCL->db_prepare_input($_POST['artist_role'][$key]), $databaseCL->db_prepare_input($_GET['rel_id']), $framework->generateUserName($databaseCL->db_prepare_input($name)), $databaseCL->db_prepare_input($_POST['username'][$key]));
										} else {
											$sql = sprintf("INSERT INTO new_release_artists (`name`, `role`, `release_id`, `username`) VALUES ('%s', '%s', '%s', '%s')", $databaseCL->db_prepare_input($name), $databaseCL->db_prepare_input($_POST['artist_role'][$key]), $databaseCL->db_prepare_input($_GET['rel_id']), $framework->generateUserName($databaseCL->db_prepare_input($name)));
										}
										$databaseCL->dbProcessor($sql, 0, 1);
									}
								}
							} else {
								echo 1;
							}
							$release_id = $databaseCL->db_prepare_input($_GET['rel_id']);
							$title = $databaseCL->db_prepare_input($_POST['title']);
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
								$msg = 'Please enter a title for this release';
							} elseif ($_POST['artist'][0] == '') {
								$msg = 'Please add a primary artist to the release';
							} elseif ($c_line == '') {
								$msg = 'Please enter your composition copyright';
							} elseif ($p_line == '') {
								$msg = 'Please enter your sound recording copyright';
							} elseif ($label == '') {
								$msg = 'Please enter a record label for this release';
							} elseif ($release_date == '') {
								$msg = 'Please set a release date for this release';
							} else {
								$sql = sprintf("UPDATE new_release SET `title` = '%s', `description` = '%s', `p_genre` = '%s', `s_genre` = '%s', `p_line` = '%s', `c_line` = '%s', `p_line_year` = '%s', `c_line_year` = '%s', `label` = '%s', `release_date` = '%s', `explicit` = '%s' WHERE `release_id` = '%s'", $title, $desc, $p_genre, $s_genre, $p_line, $c_line, $p_line_year, $c_line_year, $label, $release_date, $explicit, $release_id);
								$databaseCL->dbProcessor($sql, 0, 1);
								$msg = 'Your information has been saved';$t = 1;
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
							$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$release_id.''), 1);
						}
						$publisher = new themer('distribution/new_release_publish'); 
						$PTMPL['publisher'] = $publisher->make();
					}
					if (isset($_POST['free_distribution'])) {
						$sql = sprintf("UPDATE new_release SET `status` = '2' WHERE `release_id` = '%s'", $release_id);
						$set = $databaseCL->dbProcessor($sql, 0, 2);
						if ($set == 1) {
							$PTMPL['publisher'] = bigNotice('Your release has been submitted for moderation!', 1, 'bg-white shadow');
						} elseif ($set == 0) {
							$PTMPL['publisher'] = bigNotice('Your release has already been submitted for moderation, if you made changes we are also tracking those changes!', 2, 'bg-white shadow');
						} else {
							$PTMPL['publisher'] = bigNotice($set, 3, 'bg-white shadow');
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
							$msg = 'Your '.$post_what.' has been created. Playlists created from this tool are featured playlists and are made available in such manner that more users of our sharing site are able to find them, you can login to the sharing site to manage and add tracks to your playlists!';
							$t = 1; 
						}
						$set = $databaseCL->dbProcessor($sql, 0, 1);
						if ($post_what == 'release') {
							$msg = 'Your '.$post_what.' has been created';
							$framework->redirect(cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$release_id.''), 1);
						}
					}
					if (isset($set)) {
						$msg = $set == 1 ? $msg : $set;
					}
					$PTMPL['notification'] = bigNotice($msg, $t); 
				}
				$theme = new themer('distribution/new_release'); 
			} elseif ($_GET['action'] == 'releases') {
				$theme = new themer('distribution/all_releases');
			} else {
				$theme = new themer('distribution/content');
			}
		} else {
			$theme = new themer('distribution/content');
		}
	}
	return $theme->make();
}
?>
