<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $admin, $user, $user_role, $framework, $databaseCL, $marxTime; 

	$databaseCL->get_all = true;
	$framework->get_all = true;

   	if ($admin || $user_role >= 4) {
   	 	$PTMPL['upload_script'] = $SETT['url'].'/connection/ckuploader.php?action=ckeditor';
			
		$PTMPL['page_title'] = $LANG['administrator'];
		$PTMPL['site_url'] = $SETT['url'];

		if (isset($_GET['view'])) {
        	$PTMPL['reset_view'] = cleanUrls($SETT['url'].'/index.php?page=admin&view='.$_GET['view']); 
		}
		
		$page = $SETT['url'].$_SERVER['REQUEST_URI'];

		$s_id = isset($_GET['static_id']) && $_GET['static_id'] !== '' ? $_GET['static_id'] : null; 
		$get_statics = $databaseCL->fetchStatic($s_id)[0];

		$option = $option_var = $opt_var = $class = $PTMPL['notification'] = '';
		$excess_['ap'] = $excess_['cp'] = 0; 

		// $PTMPL['categories'] = $databaseCL->categoryOptions($array_container_a_category_row);
		
		$PTMPL['return_btn'] = cleanUrls($SETT['url'].'/index.php?page=moderate');
		$delete_btn = '<button type="submit" name="delete" class="btn btn-danger my-4 btn-block"><i class="fa fa-trash"></i> Delete</a>';

		// Set parents select options for static content
		$parents = array(
			'about' 	=> 	'About Page Section', 
			'contact' 	=> 	'Contact Page Section',
			'static' 	=> 	'New static Page',
			'events'	=>	'Event Header',
			'footer'	=>	'Footer Text'
		);
		foreach ($parents as $key => $row) { 
			$sel = (isset($_POST['parent']) && $_POST['parent'] == $key) || ($get_statics['parent'] == $key) ? ' selected="selected"' : ''; 
			$option_var .= '<option value="'.$key.'"'.$sel.'>'.$row.'</option>';
		}
		$PTMPL['static_parents'] = $option_var; 

		// Set priority select options for static content
		$parents = array(0, 1, 2, 3, 4, 5);
		foreach ($parents as $key => $row) { 
			$sel = (isset($_POST['priority']) && $_POST['priority'] == $row) || ($get_statics['priority'] == $row) ? ' selected="selected"' : ''; 
			$opt_var .= '<option value="'.$row.'"'.$sel.'>'.$row.'</option>';
		}
		$PTMPL['priority'] = $opt_var; 

		// Set icons for static content
		$set_icon = isset($_POST['icon']) ? $_POST['icon'] : $get_statics['icon'] ? $get_statics['icon'] : ''; 
		$PTMPL['icons'] = icon(1, $set_icon);

		if (isset($_GET['view'])) {
			if ($_GET['view'] == 'config') {
				$PTMPL['page_title'] = 'Site Configuration'; 

				// Set config option to update
				$sett = $alld = '';
				$allowed = $databaseCL->dbProcessor("SELECT * FROM allowed_config", 1); 
				foreach ($configuration as $key => $config) { 
					if (isset($_POST['setting'])) {
						$sel = (isset($_POST['setting']) && $_POST['setting'] == $key) ? ' selected="selected"' : ''; 
					} else {
						$sel = (isset($_POST['allowed_setting']) && $_POST['allowed_setting'] == $key) ? ' selected="selected"' : ''; 
					}
					$marxTime->explode = '_';
					$title = ucwords($marxTime->reconstructString($key));

					if ($admin['level'] == 1) {
						$sett .= '<option value="'.$key.'"'.$sel.'>'.$title.'</option>';
						if (in_array($key, $marxTime->dekeyArray($allowed))) {
							$alld .= '<option value="'.$key.'" class="text-success"'.$sel.'>'.$title.'</option>';
						} else {
							$alld .= '<option value="'.$key.'"'.$sel.'"">'.$title.'</option>';
						}
					} else {
						if (in_array($key, $marxTime->dekeyArray($allowed))) {
							$sett .= '<option value="'.$key.'"'.$sel.'>'.$title.'</option>';
						} else {
							$sett .= '<option disabled>'.$title.'</option>';
						}
					}
				}

				$PTMPL['settings'] = $sett;
 
				// Set variables to show that this update is an image 
				$clear_image = '';
				if (isset($_POST['setting'])) { 

					$selectables = array(
						'ads_off', 'allow_login', 'rave_mode', 'smtp_auth', 'sms', 
						'smtp', 'smtp_secure', 'captcha', 'fbacc', 'clean_url', 'cleanurl'
					);
					$imageables = array(
						'logo', 'intro_logo', 'banner', 'intro_banner', 'image'
					);
					$textareable = array(
						'site_office', 'tracking', 'ads_1', 'ads_2', 'ads_3', 'ads_4'
					);

					if (in_array($_POST['setting'], $selectables)) {
						$this_is_a_select = 1; 
					} elseif (in_array($_POST['setting'], $imageables)) {
						$this_is_an_image = 1;
						$clear_image = 
						'<button class="btn btn-danger my-4 btn-block flex-grow-1" type="submit" name="clear_image">Clear Image</button>';
					} elseif (in_array($_POST['setting'], $textareable)) {
						$this_is_a_text_field = 1;
					}
				} 

				// Buttons to show or update the configuration
				$PTMPL['conf_btn'] = isset($_POST['view']) ? 
				$clear_image.
				'<button class="btn btn-dark-green my-4 btn-block flex-grow-1" type="submit" name="update">Update</button>' : 
				'<button class="btn btn-success my-4 btn-block flex-grow-1" type="submit" name="view">View Setting</button>';

				if ($admin['level'] == 1) { 

					// Set the configuration allowed for lower admin
					if (isset($_POST['show_btn'])) {
						$allow_btn = '
						<div class="col">
	                      	<button class="btn btn-success btn-md" type="submit" name="allow">Allow</button>
	                    </div>
	                    <div class="col">
	                      	<button class="btn btn-danger btn-md" type="submit" name="remove">Remove</button>
	                    </div>';
					} else {
						$allow_btn = '
						<div class="col">
	                      	<button class="btn btn-success btn-md" type="submit" name="show_btn">Show Actions</button>
	                    </div>';						
					}
					$allowed = '';
					$PTMPL['allowed_conf'] = '
					<form style="color: #757575;" method="post" action="">
						<label for="select">Set Allowed Configuration</label>
	                  	<div class="form-row mb-4 text-center">
	                    	<div class="col-md-5">
								<select class="browser-default custom-select mt-1" id="select" name="allowed_setting">
									<option disabled>Choose a setting</option>
									'.$alld.'
								</select>
							</div>
							'.$allow_btn.'
						</div>
					</form>';

					if (isset($_POST['allow']) || isset($_POST['remove'])) { 
						$value = $_POST['allowed_setting'];
						if ($value != '') {
							if (isset($_POST['allow'])) {
								$msg = ucwords($marxTime->reconstructString($value)).' has been allowed';
								$allowed = $databaseCL->dbProcessor("INSERT INTO allowed_config (`name`) VALUES ('$value')", 0, $msg);
							} elseif (isset($_POST['remove'])) {
								$msg = ucwords($marxTime->reconstructString($value)).' has been removed';
								$allowed = $databaseCL->dbProcessor("DELETE FROM allowed_config WHERE `name` = '$value'", 0, $msg);
							}
							$PTMPL['notification'] = (isset($allowed) ? messageNotice($allowed) : '');
						}
					}
				} else { 
					$PTMPL['notification'] = messageNotice('Some settings have been disabled, due to their sensitivity and risk of breaking the site!', 2, 7);
				}

				$PTMPL['conf_value'] = '';
				if (isset($_POST['view'])) {
					$PTMPL['conf_value'] = $configuration[$_POST['setting']]; 
				} elseif (isset($_POST['update']) || isset($_POST['clear_image'])) {
					// Save the new image
					if (isset($_POST['clear_image'])) { 
						$set_image = null;
					} elseif (isset($_FILES['image'])) {
						$image = $framework->imageUploader($_FILES['image'], 1);
						if (is_array($image)) {  
							deleteFile($configuration[$_POST['setting']], 3); 
							$set_image = $image[0];
						} else {
							if (isset($this_is_an_image) && isset($image)) {
								$errors = messageNotice($image);
							}
							if (isset($configuration[$_POST['setting']])) {
								$set_image = $configuration[$_POST['setting']];
							} else {
								$set_image = null;
							}
						}
					}
 
					if (isset($errors)) {
						$PTMPL['notification'] = $errors;
					} else {
						$PTMPL['conf_value'] = $value = isset($_POST['value']) ? $_POST['value'] : $set_image;
						if (isset($_POST['setting']) && $value != '' || !isset($set_image)) {
							$sql = sprintf("UPDATE configuration SET `%s` = '%s'", $_POST['setting'], addslashes($value));
							$set = $databaseCL->dbProcessor($sql, 0, 1);
							$PTMPL['notification'] = $set == 1 ? messageNotice('Configuration Updated', 1) : messageNotice($set);
						}
					}
				}

				// Determine to show text field or upload form
				if (isset($_POST['view']) || isset($_POST['update'])) { 
 
					if ($PTMPL['conf_value'] == '0') {
						$cst = 'Off';
					} elseif ($PTMPL['conf_value'] == '1') {
						$cst = 'On';
					} else {
						$cst = $PTMPL['conf_value'];
					}

					$cst = $_POST['setting'] == 'tracking' ? '<br><code>'.htmlspecialchars($cst).'</code>' : $cst;
					$PTMPL['current_setting'] = 
					'<h4><div class="container border border-dark p-3 rounded bg-light"> Current Setting: <span class="text-dark"> '.$cst.' </span></div></h4>';

					if (isset($this_is_an_image)) {
						$post_value = ucwords($marxTime->reconstructString($_POST['setting']));
						$PTMPL['input_field'] = '
						<label for="upload-col">Upload '.$post_value.' Image</label>
						<div class="input-group mb-4" id="upload-col">
							<div class="input-group-prepend">
								<span class="input-group-text">Choose '.$post_value.' File</span>
							</div>
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="fileInput" aria-describedby="fileInput" name="image">
								<label class="custom-file-label" for="fileInput">File Name</label>
							</div>
						</div>';
					} elseif (isset($this_is_a_text_field)) {
						$PTMPL['input_field'] = '
						<div class="mb-4 mx-0">
							<label for="content_title">New Value</label>
							<textarea id="value" class="form-control" placeholder="New Value" name="value" row="3" required>'.$PTMPL['conf_value'].'</textarea>
							<div class="mt-0 invalid-feedback">
								Please provide a valid value.
							</div>
						</div>';
					} elseif (isset($this_is_a_select)) {
						if ($_POST['setting'] == 'smtp_secure') {
							$opts = '
								<option value="0">Off</option>
								<option value="ssl">SSL</option>
								<option value="tls">TLS</option>
							';
						} else {
							$opts = '
								<option value="0">Off</option>
								<option value="1">On</option>
							';							
						}
						$PTMPL['input_field'] = '
						<div class="mb-4 mx-0">
							<label for="content_title">New Value</label>
							<select id="value" class="browser-default custom-select" name="value" required>
								'.$opts.'
							</select> 
							<div class="mt-0 invalid-feedback">
								Please provide a valid value.
							</div>
						</div>';						
					} else {
						$PTMPL['input_field'] = '
						<div class="mb-4 mx-0">
							<label for="content_title">New Value</label>
							<input type="text" id="value" class="form-control" placeholder="New Value" name="value" value="'.$PTMPL['conf_value'].'" required>
							<div class="mt-0 invalid-feedback">
								Please provide a valid value.
							</div>
						</div>';
					} 
				}

				$theme = new themer('admin/config');

			} 
			elseif ($_GET['view'] == 'releases') {
				// Show the list of created posts

				$PTMPL['page_title'] = 'Manage Releases';

				$theme = new themer('distribution/global/scripts');
				$PTMPL['body_scripts'] = $theme->make();

				if (isset($_GET['delete'])) {
					$did = $databaseCL->db_prepare_input($_GET['delete']);
					$delete = $databaseCL->deleteContent($did);
					if ($delete === 1) {
						$PTMPL['notification'] = messageNotice('Content has been deleted successfully', 1, 6);
					} elseif ($delete === 0) {
						$PTMPL['notification'] = messageNotice('Content does not exist, or may have already been deleted', 2, 6);
					} else {
						$PTMPL['notification'] = messageNotice($delete, 3, 7);
					}
				}

		        $create_post_link = cleanUrls($SETT['url'].'/index.php?page=distribution&action=new_release');
		        $PTMPL['create_rel_btn'] = '<a href="'.$create_post_link.'" class="btn btn-primary font-weight-bolder mb-2">Create new release</a>';

				if (isset($_POST['search'])) {  
					$q = $framework->urlRequery('&q='.$_POST['q']);
					$framework->redirect($q, 1); 
				} 
				$PTMPL['search_str'] = isset($_GET['q']) ? $_GET['q'] : '';

				$PTMPL['releases_list'] = $databaseCL->manageRealeases();

				if (isset($_GET['action'])) {
					if ($_GET['action'] == 'approve') {
						$action = $databaseCL->salesAction($_GET['rel_id'], 1);
						if ($action) {
							$PTMPL['notification'] = messageNotice('The release has been approved and is being sent to the stores', 1, 7);
						}
					} elseif ($_GET['action'] == 'remove') {
						$action = $databaseCL->salesAction($_GET['rel_id'], 2);		
						if ($action) {
							$PTMPL['notification'] = messageNotice('The release has been removed from sale and is being removed from the stores', 1, 7);
						}
					} elseif ($_GET['action'] == 'delete') {
						$action = $databaseCL->salesAction($_GET['rel_id']);		
						if ($action) {
							$PTMPL['notification'] = messageNotice('The release has been deleted', 1, 7);
						}
					}
				}

				$theme = new themer('admin/release_content');
			} 
			elseif ($_GET['view'] == 'static') {
				// Show the list of static pages
				// 

				$databaseCL->parent = 'about'; $databaseCL->priority = '3';
				$ex_about =  $databaseCL->fetchStatic(1);
				if ($ex_about && count($ex_about) > 1) {
					$excess_['ap'] = 1;
					$PTMPL['notification'] = messageNotice($LANG['excess_about_priority'], 3, 6);
				}
				$databaseCL->parent = 'contact'; $databaseCL->priority = '3';
				$ex_cont =  $databaseCL->fetchStatic(1);
				if ($ex_cont && count($ex_cont) > 1) {
					$excess_['cp'] = 1;
					$PTMPL['notification'] .= messageNotice($LANG['excess_contact_priority'], 3, 6);
				}
				$databaseCL->parent = $databaseCL->priority = null;

				if (isset($_GET['delete'])) {
					$did = $databaseCL->db_prepare_input($_GET['delete']);
					$delete = $databaseCL->deleteContent($did, 1);
					if ($delete === 1) {
						$PTMPL['notification'] = messageNotice('Content has been deleted successfully', 1, 6);
					} elseif ($delete === 0) {
						$PTMPL['notification'] = messageNotice('Content does not exist, or may have already been deleted', 2, 6);
					} else {
						$PTMPL['notification'] = messageNotice($delete, 3, 7);
					}
				}
 				
 				// Perform a search on the static table
				if (isset($_POST['search'])) {
					$q = $framework->urlRequery('&q='.$_POST['q']);
					$framework->redirect($q, 1); 
				} 
				$PTMPL['search_str'] = isset($_GET['q']) ? $_GET['q'] : '';

				if (isset($_GET['q'])) {
					$databaseCL->search_query = $_GET['q'];
				} 
				if (isset($_POST['filter'])) {
					$databaseCL->filter_query = $_POST['f'];		
				}

				$PTMPL['page_title'] = 'Manage static content';
			    $framework->all_rows = $databaseCL->fetchStatic(null, 1);
			    $PTMPL['pagination'] = $framework->pagination(); 
				$list_statics = $databaseCL->fetchStatic(null, 1);

				if ($list_statics) {
					$table_row_static = ''; $i=0;
					foreach ($list_statics as $sta) {
						$i++; 
						if (isset($_GET['delete'])) {
							$delete_link = cleanUrls(str_replace('&delete='.$_GET['delete'], '', $page).'&delete='.$sta['id']);
						} else {
							$delete_link = cleanUrls($page.'&delete='.$sta['id']);
						}
						$edit_link = cleanUrls($SETT['url'].'/index.php?page=admin&view=create_static&static_id='.$sta['id']);
						$set_view = $sta['parent'] == 'about' || $sta['parent'] == 'contact' ? $sta['parent'] : $sta['safelink'];
						$view_link = cleanUrls($SETT['url'].'/index.php?page=static&view='.$set_view);

						// Highlight items exceeding normal usage
						foreach (array('ap' => 'about', 'cp' => 'contact') as $k => $r) {
							if ($excess_[$k] && ($sta['parent'] == $r && $sta['priority'] == 3)) {
								$class = ' class="text-danger font-weight-bold"';
							}
						}

						// Generate the table
						$table_row_static .= '
						<tr>
							<th scope="row">'.$i.'</th>
							<td><a href="'.$view_link.'" title="View Content"'.$class.'>'.$sta['title'].'</a></td>
							<td>'.$sta['parent'].'</td>
							<td class="'.$framework->mdbColors($sta['priority']).'">'.$sta['priority'].'</td>
							<td class="d-flex justify-content-around">
								<a href="'.$edit_link.'" title="Edit Content"><i class="fa fa-edit text-info hoverable"></i></a>
								<a href="'.$delete_link.'" title="Delete Content"><i class="fa fa-trash text-danger hoverable"></i></a> 
							</td>
						</tr>';
					}
					$PTMPL['static_list'] = $table_row_static;
				}

		        $create_static_link = cleanUrls($SETT['url'].'/index.php?page=admin&view=create_static');
		        $PTMPL['create_static_btn'] = '<a href="'.$create_static_link.'" class="btn btn-primary font-weight-bolder mb-2">Create new static content</a>';

				$theme = new themer('admin/static_content');
			} 
			elseif ($_GET['view'] == 'manage_users') {
				$theme = new themer('admin/manage_users');

				// Set the page title
				$PTMPL['page_title'] = $PTMPL['page_title'].'(Manage Users)';

				// Notification Form
				$noti_form = new themer('admin/notification_form');
				$notification_form = $noti_form->make();
				$PTMPL['notification_form'] = modal('sendMsg', $notification_form, 'Send Notification', 2);

				// Perform a search or filter on the database
				$filter = '';
				if (isset($_POST['filter'])) {
					$filter .= sprintf(" AND `role` = '%s'", $_POST['f']);		
				}
				if (isset($_GET['q'])) {
					$filter .= sprintf(" AND `uid` = '%s' OR `username` LIKE '%s' OR concat_ws(' ', fname, lname) LIKE '%s'", $_GET['q'], '%'.$_GET['q'].'%', '%'.$_GET['q'].'%');
				} 
				$framework->filter = $filter;
			    $framework->all_rows = $framework->userData(0, 0);
			    $PTMPL['pagination'] = $framework->pagination(); 
				$user_records = $framework->userData(0, 0);

				// Query to update the users status
				if (isset($_GET['verify'])) {
					$set_verify = $framework->dbProcessor(sprintf("UPDATE users SET `verified` = '%s' WHERE `uid` = '%s'", $_GET['verify'], $_GET['user_id']), 0, 1);
					if ($set_verify == 1) {
						$ver_state = $_GET['verify'] ? 'verified' : 'unverified';
						$PTMPL['notification'] = messageNotice('User has been '.$ver_state, 1);
					} else {
						$PTMPL['notification'] = messageNotice($set_verify);
					}
				}

				// Delete a user and all their records
				if (isset($_GET['delete'])) {
					$set_delete = $databaseCL->deleteUser($_GET['delete']);
					if ($set_delete === TRUE) {
						$PTMPL['notification'] = messageNotice('User has been deleted', 1);
					} elseif ($set_delete === FALSE) {
						$PTMPL['notification'] = messageNotice('<b>Error:</b> User could not be deleted', 3);
					} else {
						$PTMPL['notification'] = $set_delete;
					}
				}

 				// Perform a search
				if (isset($_POST['search'])) {
					$q = $framework->urlRequery('&q='.$_POST['q']);
					$framework->redirect($q, 1); 
				} 
				$PTMPL['search_str'] = isset($_GET['q']) ? $_GET['q'] : '';
				if ($user_records) {
					$table_row_users = ''; $i=0;
					foreach ($user_records as $users) {
						$i++; 
						$delete_link = $framework->urlRequery('&delete='.$users['uid']);
						$edit_link = cleanUrls($SETT['url'].'/index.php?page=distribution&action=management_tools&update_user='.$users['uid']); 
						$view_link = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$users['username']);
						$fullname = $framework->realName($users['username'], $users['fname'], $users['lname']);
						$user_role = $framework->userRoles(0, $users['uid']);

						// Show and change the users verification status
						if ($users['verified']) {
							$verified = '<i class="fa fa-check-circle text-success"></i>';
							$verify_link = $framework->urlRequery('&verify=0&user_id='.$users['uid']);
							$ver_class = 'fa-check-circle-o text-danger';
						} else {
							$verified = 'No';
							$verify_link = $framework->urlRequery('&verify=1&user_id='.$users['uid']);
							$ver_class = 'fa-check-circle-o text-success';
						}
						$reg_date = $marxTime->dateFormat($users['reg_date'], 1); 
						$message_btn = ' <a class="send_msg_notn" data-receiver="'.$users['uid'].'" data-username="'.$fullname.'" href="#" title="Send Notification"><i class="fa fa-envelope-open text-info hoverable"></i></a>';

						// Generate the table
						$table_row_users .= '
						<tr>
							<th scope="row">'.$i.'</th>
							<td><a href="'.$view_link.'" title="View User"'.$class.'>'.$fullname.$message_btn.'</a></td>
							<td>'.$users['label'].'</td>
							<td class="'.$framework->mdbColors($users['role']).'">'.$user_role.'</td>
							<td>'.$verified.'</td>
							<td>'.$reg_date.'</td>
							<td class="d-flex justify-content-around">
								<a href="'.$verify_link.'" title="Verify/Unverify User"><i class="fa fa-2x '.$ver_class.' hoverable"></i></a>
								<a href="'.$edit_link.'" title="Edit User"><i class="fa fa-2x fa-edit text-info hoverable"></i></a>
								<a href="'.$delete_link.'" title="Delete User"><i class="fa fa-2x fa-trash text-danger hoverable"></i></a> 
							</td>
						</tr>';
					}
					$PTMPL['users_list'] = $table_row_users;
				}
			}
			elseif ($_GET['view'] == 'manage_tracks') {
				$theme = new themer('admin/manage_tracks');

				// Set the page title
				$PTMPL['page_title'] = $PTMPL['page_title'].'(Manage Tracks)';

				if (isset($_GET['q'])) {
					$databaseCL->filter = sprintf(" AND `title` LIKE '%s' OR `tracks`.`label` LIKE '%s'", '%'.$_GET['q'].'%', '%'.$_GET['q'].'%');
				} 
				// $framework->limit_records =2;
			    $framework->all_rows = $databaseCL->fetchTracks(0, 6);
			    $PTMPL['pagination'] = $framework->pagination(); 
				$track_list = $databaseCL->fetchTracks(0, 6);

				// Query to update the users status
				if (isset($_GET['feature'])) {
					$set_feature = $framework->dbProcessor(sprintf("UPDATE tracks SET `featured` = '%s' WHERE `id` = '%s'", $_GET['feature'], $_GET['track_id']), 0, 1);
					if ($set_feature == 1) {
						$feat_state = $_GET['feature'] ? 'added to featured list' : 'removed from featured list';
						$PTMPL['notification'] = messageNotice('Track has been '.$feat_state, 1);
					} else {
						$PTMPL['notification'] = messageNotice($set_feature);
					}
				}
				// Delete a user and all their records
				if (isset($_GET['delete'])) {
					$set_delete = $databaseCL->deleteTrack($_GET['delete']);
					if ($set_delete === TRUE) {
						$PTMPL['notification'] = messageNotice('Track has been deleted', 1);
					} elseif ($set_delete === FALSE) {
						$PTMPL['notification'] = messageNotice('<b>Error:</b> Track could not be deleted', 3);
					} else {
						$PTMPL['notification'] = messageNotice($set_delete);
					}
				}

 				// Perform a search
				if (isset($_POST['search'])) {
					$q = $framework->urlRequery('&q='.$_POST['q']);
					$framework->redirect($q, 1); 
				} 
				$PTMPL['search_str'] = isset($_GET['q']) ? $_GET['q'] : '';

				if ($track_list) {
					$table_row_tracks = ''; $i=0;
					foreach ($track_list as $track) {
						$i++; 
						$delete_link = $framework->urlRequery('&delete='.$track['id']);
						$edit_link = cleanUrls($SETT['url'].'/index.php?page=distribution&action=management_tools&update_track='.$track['id']); 
						$track_link = cleanUrls($SETT['url'].'/index.php?page=track&track='.$track['safe_link']);
						$user_link = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$track['username']);
						$fullname = $framework->realName($track['username'], $track['fname'], $track['lname']);
						$views = $databaseCL->fetchStats(1, $track['id'])[0]; 

						// Show and change the users verification status
						if ($track['featured']) { 
							$featured_link = $framework->urlRequery('&feature=0&track_id='.$track['id']);
							$ver_class = 'fa-check-circle-o text-danger';
						} else { 
							$featured_link = $framework->urlRequery('&feature=1&track_id='.$track['id']);
							$ver_class = 'fa-check-circle-o text-success';
						}
						$rel_date = $marxTime->dateFormat($track['release_date'], 1); 

						// Generate the table
						$table_row_tracks .= '
						<tr>
							<th scope="row">'.$i.'</th> 
							<td><a href="'.$track_link.'" title="View Track"'.$class.'>'.$track['title'].'</a></td>
							<td><a href="'.$user_link.'" title="View Artist"'.$class.'>'.$fullname.'</a></td>
							<td>'.$track['label'].'</td>
							<td>'.$views['total'].'</td>
							<td>'.$rel_date.'</td>
							<td>'.($track['public'] ? 'Yes' : 'No').'</td>
							<td>'.($track['featured'] ? 'Yes' : 'No').'</td>
							<td class="d-flex justify-content-around">
								<a href="'.$featured_link.'" title="Feature/Unfeature Track"><i class="fa fa-2x '.$ver_class.' hoverable"></i></a>
								<a href="'.$edit_link.'" title="Edit Track"><i class="fa fa-2x fa-edit text-info hoverable"></i></a>
								<a href="'.$delete_link.'" title="Delete Track"><i class="fa fa-2x fa-trash text-danger hoverable"></i></a> 
							</td>
						</tr>';
					}
					$PTMPL['tracks_list'] = $table_row_tracks;
				}
			}
			elseif ($_GET['view'] == 'manage_projects') {
				$theme = new themer('admin/manage_projects');

				// Set the page title
				$PTMPL['page_title'] = $PTMPL['page_title'].'(Manage Projects)';

				if (isset($_GET['q'])) {
					$databaseCL->filter = sprintf(" AND `title` LIKE '%s' OR `tags` LIKE '%s'", '%'.$_GET['q'].'%', '%'.$_GET['q'].'%');
				} 
 
			    $framework->all_rows = $databaseCL->fetchProject(0, 2);
			    $PTMPL['pagination'] = $framework->pagination(); 
				$project_list = $databaseCL->fetchProject(0, 2);

				// Query to update the users status
				if (isset($_GET['recommend'])) {
					$set_recommend = $framework->dbProcessor(sprintf("UPDATE projects SET `recommended` = '%s' WHERE `id` = '%s'", $_GET['recommend'], $_GET['project_id']), 0, 1);
					if ($set_recommend == 1) {
						$rec_state = $_GET['recommend'] ? 'added to recommended list' : 'removed from recommended list';
						$PTMPL['notification'] = messageNotice('Project has been '.$rec_state, 1);
					} else {
						$PTMPL['notification'] = messageNotice($set_recommend);
					}
				}
				// Delete a user and all their records
				if (isset($_GET['delete'])) {
					$set_delete = $databaseCL->deleteProject($_GET['delete']);
					if ($set_delete === TRUE) {
						$PTMPL['notification'] = messageNotice('Project has been deleted', 1);
					} elseif ($set_delete === FALSE) {
						$PTMPL['notification'] = messageNotice('<b>Error:</b> Project could not be deleted', 3);
					} else {
						$PTMPL['notification'] = messageNotice($set_delete);
					}
				}

 				// Perform a search
				if (isset($_POST['search'])) {
					$q = $framework->urlRequery('&q='.$_POST['q']);
					$framework->redirect($q, 1); 
				} 
				$PTMPL['search_str'] = isset($_GET['q']) ? $_GET['q'] : '';

				if ($project_list) {
					$table_row_tracks = ''; $i=0;
					foreach ($project_list as $project) {
						$i++; 
						$delete_link = $framework->urlRequery('&delete='.$project['id']);
						$edit_link = cleanUrls($SETT['url'].'/index.php?page=admin&view=manage_projects&update_project='.$project['id']); 
						$track_link = cleanUrls($SETT['url'].'/index.php?page=project&project='.$project['safe_link']);
						$user_link = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$project['username']);
						$fullname = $framework->realName($project['username'], $project['fname'], $project['lname']); 

						// Show and change the users verification status
						if ($project['recommended']) { 
							$recomend_link = $framework->urlRequery('&recommend=0&project_id='.$project['id']);
							$ver_class = 'fa-check-circle-o text-danger';
						} else { 
							$recomend_link = $framework->urlRequery('&recommend=1&project_id='.$project['id']);
							$ver_class = 'fa-check-circle-o text-success';
						}
						$rel_date = $marxTime->dateFormat($project['time'], 1); 

						// Generate the table
						$table_row_tracks .= '
						<tr>
							<th scope="row">'.$i.'</th> 
							<td><a href="'.$track_link.'" title="View Project"'.$class.'>'.$project['title'].'</a></td>
							<td><a href="'.$user_link.'" title="View Creator"'.$class.'>'.$fullname.'</a></td> 
							<td>'.$project['count_stems'].'</td>
							<td>'.$project['count_instrumentals'].'</td>
							<td>'.($project['status'] ? 'Active' : 'Inactive').'</td>
							<td>'.$rel_date.'</td>
							<td>'.($project['published'] ? 'Yes' : 'No').'</td>
							<td>'.($project['recommended'] ? 'Yes' : 'No').'</td>
							<td class="d-flex justify-content-around">
								<a href="'.$recomend_link.'" title="Recommend/Unrecommend Track"><i class="fa fa-2x '.$ver_class.' hoverable"></i></a>
								<a href="'.$edit_link.'" title="Edit Track"><i class="fa fa-2x fa-edit text-info hoverable"></i></a>
								<a href="'.$delete_link.'" title="Delete Track"><i class="fa fa-2x fa-trash text-danger hoverable"></i></a> 
							</td>
						</tr>';
					}
					$PTMPL['project_list'] = $table_row_tracks;
				}
			}
			elseif ($_GET['view'] == 'manage_playlists') {
				$theme = new themer('admin/manage_playlists');

				// Set the page title
				$PTMPL['page_title'] = $PTMPL['page_title'].'(Manage Playlists)';

				if (isset($_GET['q'])) {
					$databaseCL->filter = sprintf(" AND `title` LIKE '%s'", '%'.$_GET['q'].'%');
				} 
 
			    $framework->all_rows = $databaseCL->fetchPlaylist(0, 2);
			    $PTMPL['pagination'] = $framework->pagination(); 
				$playlists = $databaseCL->fetchPlaylist(0, 2);

				// Query to update the users status
				if (isset($_GET['feature'])) {
					$set_feature = $framework->dbProcessor(sprintf("UPDATE playlists SET `featured` = '%s' WHERE `id` = '%s'", $_GET['feature'], $_GET['playlist_id']), 0, 1);
					if ($set_feature == 1) {
						$feat_state = $_GET['feature'] ? 'added to recommended list' : 'removed from recommended list';
						$PTMPL['notification'] = messageNotice('Project has been '.$feat_state, 1);
					} else {
						$PTMPL['notification'] = messageNotice($set_feature);
					}
				}
				// Delete a user and all their records
				if (isset($_GET['delete'])) {
					$set_delete = $databaseCL->deleteProject($_GET['delete']);
					if ($set_delete === TRUE) {
						$PTMPL['notification'] = messageNotice('Project has been deleted', 1);
					} elseif ($set_delete === FALSE) {
						$PTMPL['notification'] = messageNotice('<b>Error:</b> Project could not be deleted', 3);
					} else {
						$PTMPL['notification'] = messageNotice($set_delete);
					}
				}

 				// Perform a search
				if (isset($_POST['search'])) {
					$q = $framework->urlRequery('&q='.$_POST['q']);
					$framework->redirect($q, 1); 
				} 
				$PTMPL['search_str'] = isset($_GET['q']) ? $_GET['q'] : '';

				if ($playlists) {
					$table_row_tracks = ''; $i=0;
					foreach ($playlists as $plist) {
						$i++; 
						$delete_link = $framework->urlRequery('&delete='.$plist['id']);
						$edit_link = cleanUrls($SETT['url'].'/index.php?page=admin&view=manage_playlists&update_plist='.$plist['id']); 
						$track_link = cleanUrls($SETT['url'].'/index.php?page=playlist&playlist='.$plist['plid']);
						$user_link = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$plist['username']);
						$fullname = $framework->realName($plist['username'], $plist['fname'], $plist['lname']); 

						// Show and change the users verification status
						if ($plist['featured']) { 
							$recomend_link = $framework->urlRequery('&feature=0&playlist_id='.$plist['id']);
							$ver_class = 'fa-check-circle-o text-danger';
						} else { 
							$recomend_link = $framework->urlRequery('&feature=1&playlist_id='.$plist['id']);
							$ver_class = 'fa-check-circle-o text-success';
						} 

						// Generate the table
						$table_row_tracks .= '
						<tr>
							<th scope="row">'.$i.'</th> 
							<td><a href="'.$track_link.'" title="View Project"'.$class.'>'.$plist['title'].'</a></td>
							<td><a href="'.$user_link.'" title="View Creator"'.$class.'>'.$fullname.'</a></td> 
							<td>'.$plist['track_count'].'</td> 
							<td>'.$plist['subscribers'].'</td> 
							<td>'.($plist['public'] ? 'Yes' : 'No').'</td> 
							<td>'.($plist['featured'] ? 'Yes' : 'No').'</td> 
							<td class="d-flex justify-content-around">
								<a href="'.$recomend_link.'" title="Recommend/Unrecommend Track"><i class="fa fa-2x '.$ver_class.' hoverable"></i></a>
								<a href="'.$edit_link.'" title="Edit Track"><i class="fa fa-2x fa-edit text-info hoverable"></i></a>
								<a href="'.$delete_link.'" title="Delete Track"><i class="fa fa-2x fa-trash text-danger hoverable"></i></a> 
							</td>
						</tr>';
					}
					$PTMPL['project_list'] = $table_row_tracks;
				}
			}
			elseif ($_GET['view'] == 'create_static') {
				$theme = new themer('admin/create_static');

				// Set the page title
				$PTMPL['page_title'] = $PTMPL['page_title'].'(Create Static Content)';

				$PTMPL['up_btn'] = $get_statics ? 'Update Content' : 'Create Content';
				$PTMPL['page_title'] = $get_statics ? 'Update '.$get_statics['title'] : 'Create new Static Content';
				$PTMPL['banner'] = $get_statics ? '<img src="'.getImage($get_statics['banner'], 1).'" width="auto" height="100px" class="thumbnail"><br>' : '';
				$PTMPL['banner_buttons'] = $get_statics['button_links'];
				$PTMPL['content_title'] = isset($_POST['title']) ? $_POST['title'] : $get_statics['title']; 
				$PTMPL['main_content'] = isset($_POST['main_content']) ? $_POST['main_content'] : $get_statics['content']; 
				$PTMPL['footer_check'] = isset($_POST['footer']) || $get_statics['footer'] == 1 ? ' checked' : '';
				$PTMPL['header_check'] = isset($_POST['header']) || $get_statics['header'] == 1 ? ' checked' : ''; 

				if (isset($_POST['create_content'])) {  
					$databaseCL->parent = $_POST['parent'];
					$databaseCL->priority = $_POST['priority'];
					$databaseCL->icon = $_POST['icon'];
					$databaseCL->title = $_POST['title'];
					$databaseCL->banner_buttons = $_POST['banner_buttons'];
					$databaseCL->main_content = str_replace('\'', '', $_POST['main_content']);
					$databaseCL->image = $_FILES['image'];
					$databaseCL->banner_buttons = $_POST['banner_buttons'];
					$databaseCL->footer = isset($_POST['footer']) ? 1 : 0;
					$databaseCL->header = isset($_POST['header']) ? 1 : 0;

					$create = $databaseCL->createStaticContent();
					$PTMPL['notification'] = $create;
				}
				if ($get_statics['banner']) {
					$sid_ = $get_statics['id'];
					$PTMPL['remove_banner_btn'] = '<button class="btn btn-secondary my-4 btn-block" type="submit" name="remove_banner">Remove Banner</button>';
					if (isset($_POST['remove_banner'])) {
						$databaseCL->dbProcessor("UPDATE static_pages SET `banner` = NULL WHERE `id` = '$sid_'", 0, 1);
						deleteFile($get_statics['banner'], 1);
					}
				}
			} elseif ($_GET['view'] == 'categories') {
				$theme = new themer('admin/categories');

				// Set the page title
				$PTMPL['page_title'] = $PTMPL['page_title'].'(Manage Categories)';

	    		$page = $SETT['url'].$_SERVER['REQUEST_URI'];
	    		$set_msg = isset($_GET['msg']) ? $_GET['msg'] : '';
	    		if (isset($_POST['select_category'])) {
		    		$page = str_replace('&set='.$_GET['set'], '', $page);
		    		$page = str_replace('&msg='.$set_msg, '', $page);
	    			$framework->redirect(cleanUrls($page).'&set='.$_POST['category'], 1);
	    		}

	    		$ctid = isset($_GET['set']) ? $_GET['set'] : null;
	    		$category = $databaseCL->dbProcessor("SELECT id, title, value, info FROM categories WHERE `value` = '$ctid'", 1)[0];

				$PTMPL['page_title'] = $category ? 'Update '.$category['title'] : 'Create new Category';
				
				$PTMPL['up_btn'] = $category ? 'Update Category' : 'Create Category';
				$PTMPL['new_btn'] = cleanUrls($SETT['url'].'/index.php?page=admin&view=categories');
	    		$PTMPL['delete_btn'] = $category ? $delete_btn : '';

				$PTMPL['ct_title'] = isset($_POST['title']) ? $_POST['title'] : $category['title']; 
				$PTMPL['ct_description'] = isset($_POST['info']) ? $_POST['info'] : $category['info'];  

				if (isset($_POST['delete'])) { 
					$PTMPL['notification'] = messageNotice($databaseCL->dbProcessor("DELETE FROM categories WHERE `value` = '$ctid'", 0, 'Category Deleted'));
				}

				if (isset($_POST['create_'])) {  
					$info = $framework->db_prepare_input($_POST['info']); 
					$title = $framework->db_prepare_input($_POST['title']); 
					str_ireplace('event', '', $title, $cev_rep);
					str_ireplace('exhibition', '', $title, $cex_rep);
					if ($cev_rep > 0 || $cex_rep > 0) {
						$value = 'event';
					} else {
						$value = $framework->safeLinks($title);
					}
	 				
	 				if ($category) {
	 					$sql = "UPDATE categories SET `title` = '$title', `info` = '$info' WHERE `value` = '$ctid'";
	 					$msg = $category['title'].' has been updated';
	 				} else {
	 					$sql = "INSERT INTO categories (`title`, `value`, `info`) VALUES ('$title', '$value', '$info')";
	 					$msg = 'New category created';
	 				}

					$notification =  $databaseCL->dbProcessor($sql, 0, 1);
					if ($notification == 1) {
						if ($category) {
							$up = 2;
						} else {
							$up = 1;
						}
					} else {
						$up = 0;
					}

		    		if ($category) {
		    			$page = str_replace('&set='.$_GET['set'], '', $page);
		    			$page = str_replace('&msg='.$set_msg, '', $page);
		    			$framework->redirect(cleanUrls($page).'&set='.$value.'&msg='.$up, 1);
		    		}
				}

				if (isset($notification)) {
					$PTMPL['notification'] = messageNotice($msg);
				} elseif (isset($_GET['msg'])) {
					if ($_GET['msg'] == 1) {
						$msg = messageNotice('New category created', 1);
					} elseif ($_GET['msg'] == 2) {
						$msg = messageNotice('Selected category has been updated', 1);
					} else {
						$msg = messageNotice('You have not made any changes');
					}
					$PTMPL['notification'] = $msg;
				}
			} elseif ($_GET['view'] == 'admin') {
				$this_admin = isset($admin) ? ' ('.$admin['username'].')' : '';
				$PTMPL['page_title'] = 'Update Admin'.$this_admin; 
				
				$admin_user = $framework->userData($admin['admin_user'], 1);
				$PTMPL['lusername'] = isset($_POST['lusername']) ? $_POST['lusername'] : $admin_user['username'];

				$PTMPL['username'] = isset($_POST['username']) ? $_POST['username'] : $admin['username'];
				$PTMPL['password'] = isset($_POST['password']) ? $_POST['password'] : '';
				$PTMPL['re_password'] = isset($_POST['re_password']) ? $_POST['re_password'] : '';
					
				$na = '';
				if (isset($_POST['admin_action'])) {
					if ($_POST['admin_action'] == 'new_user') {
						$PTMPL['nu'] = ' checked';
					} elseif ($_POST['admin_action'] == 'new_admin') {
						$na = ' checked';
					} else {
						$PTMPL['ua'] = ' checked';
					}
				} else {
					$PTMPL['ua'] = ' checked';
				}
				if ($admin['level'] == 1) {
					$PTMPL['action'] = '
					<div class="custom-control custom-radio">
						<input type="radio" class="custom-control-input" id="new_admin" name="admin_action" value="new_admin"'.$na.'>
						<label class="custom-control-label" for="new_admin">Create New Admin</label>
					</div>
					'; 
				} 
					
				$admin_id = $admin['id'];
				if (isset($_POST['link'])) {
					$lusername = $framework->db_prepare_input($_POST['lusername']);
					$set_admin_user = $framework->userData($lusername, 1);
					$admin_user_id = $set_admin_user['uid'];
					$do = $framework->dbProcessor("UPDATE admin SET `admin_user` = '$admin_user_id' WHERE `id` = '$admin_id'", 0, 1);
					if ($do == 1) {
						$msg = messageNotice(ucfirst($admin['username']).' Administrative account has been linked to '.ucfirst($lusername). ' User account', 1);
					} else {
						$msg = messageNotice($do);
					}
					$PTMPL['notification'] = $msg;
				}
				if (isset($_POST['update'])) { 
					$username = $framework->db_prepare_input($_POST['username']);
					$password = hash('md5', $_POST['password']);
					$re_password = $_POST['re_password'];
					$auth = $framework->generateToken(null, 1);

					if ($_POST['re_password'] !== $_POST['password']) {
						$msg = messageNotice('Repeat password does not match with Password', 3);
					} else {
		 				if ($admin && $_POST['admin_action'] == 'update_admin') {
		 					$sql = "UPDATE admin SET `username` = '$username', `password` = '$password' WHERE `id` = '$admin_id'";
		 					$msg = messageNotice($username.' has been updated', 1);
		 				} elseif ($admin && $_POST['admin_action'] == 'new_user') {
		 					$auth_date = date('Y-m-d h:i:s', strtotime('now'));
		 					$sql = "INSERT INTO users (`username`, `password`, `role`, `auth_token`, `token_date`) VALUES ('$username', '$password', 3, '$auth', date('$auth_date'))";
		 					$msg = messageNotice('New user account created', 1);
		 				} else {
		 					$sql = "INSERT INTO admin (`username`, `password`, `auth_token`) VALUES ('$username', '$password', '$auth')";
		 					$msg = messageNotice('New admin user created', 1);
		 				}
	 					if ($_POST['admin_action'] == 'update_admin' && $username !== $admin['username'] && $framework->administrator(2, $username)) {
	 						$msg = messageNotice('This Username is already in use!');
	 					} elseif ($_POST['admin_action'] == 'new_admin' && $framework->administrator(2, $username)) {
	 						$msg = messageNotice('This Admin already exists!');
	 					} elseif ($_POST['admin_action'] == 'new_user' && $framework->userData($username, 2)) {
	 						$msg = messageNotice('This User already exists!');
	 					} else {
	 						$msg = $msg;
	 						$do = $framework->dbProcessor($sql, 0, 1);
	 						if ($do == 1) {
	 							$msg = $msg;
	 						} else {
	 							$msg = messageNotice($do);
	 						}
	 					}
	 				}
					$PTMPL['notification'] = $msg;
				}

				// Set the active landing page_title 
				$theme = new themer('admin/admin');
			} elseif ($_GET['view'] == 'filemanager') {

				// Set the page title
				$PTMPL['page_title'] = $PTMPL['page_title'].'(File Manager)';

				// Set the active landing page_title 
				$theme = new themer('admin/filemanager');
			} else {
				$framework->redirect(cleanUrls($SETT['url'].'/index.php?page=admin'), 1); 
			}
			$PTMPL['content'] = $theme->make();
		} else { 
            $category =  array( 
            	'releases' 			=> 	'View and manage releases',
            	'create_static'		=>	'New static content',
            	'static'			=>	'Manage Static content',
            	'categories'		=>	'Manage categories',
            	'filemanager'		=>	'File Manager',
            	'admin'				=>	'Update Admin Details',
            	'manage_users'		=>	'Manage Users',
            	'manage_tracks'		=>	'Manage Tracks',
			    'manage_projects'   =>  'Manage Projects',
			    'manage_playlists'  =>  'Manage Playlists',
            	'config'			=> 	array('Site Configuration', 'cog')
            ); 
            $categories = '';$i = 280;$ii = 10;
            foreach ($category as $key => $row) {
                $i++; $ii++; $icon = $i;
            	if (is_array($row)) {
            		$icon = $row[1]; 
            		$row = $row[0];
            	}
                $link = cleanUrls($SETT['url'].'/index.php?page=admin&view='.$key);  
                $categories .= '
                <div class="col-md-4 mb-4">
                    <div class="col-1 col-md-2 float-left">
                        <i class="fa '.icon(3, $icon).' fa-2x '.$framework->mdbcolors($ii).'"></i> 
                    </div> 
                    <div class="col-10 col-md-9 col-lg-10 px-3 float-right"> 
                        <a href="'.$link.'" class="btn '.$framework->mdbcolors($ii, 1).' btn-sm ml-0 p-4 px-0 font-weight-bold" style="min-height:85px; min-width: 150px;">'.$row.'</a>
                    </div>
                </div>'; 
            }
            $PTMPL['content'] = '            
            <div class="row text-left"> 
              	'.$categories.'
            </div>'; 
		}
	 
		$PTMPL['side_bar'] = admin_sidebar();
		$PTMPL['header'] = superGlobalTemplate(1);
		$PTMPL['footer'] = superGlobalTemplate();

		// Set the active landing page_title 
		$theme = new themer('admin/container');
	} else {	
		$url = $SETT['url']; // 'http://admin.collageduceemos.te';
		if (strpos($url, 'admin')) {
			if (!isset($_GET['view']) || isset($_GET['view']) && $_GET['view'] != 'access') {
				$framework->redirect(cleanUrls($SETT['url'].'/index.php?page=admin&view=access'), 1);
			}
		}

		if (isset($_GET['view']) && $_GET['view'] == 'access') { 
			$PTMPL['return_btn'] = cleanUrls($SETT['url'].'/index.php?page=homepage');

			if (isset($_GET['login']) && $_GET['login'] == 'user') {
				$PTMPL['page_title'] = 'User Login';
				$PTMPL['user_login'] = ' checked';
			} else {
				$PTMPL['page_title'] = 'Admin Login';
				$PTMPL['u_secret'] = '-secret';
			}

			if (isset($_POST['login'])) {
				$PTMPL['username'] = $username = $framework->db_prepare_input($_POST['username']);
				$PTMPL['password'] = $password = $framework->db_prepare_input($_POST['password']);

				if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
					$PTMPL['remember'] = ' checked';
					$framework->remember = 1;
				}
				$framework->username = $username;
				$framework->password = hash('md5', $password); 
				if ((isset($_GET['login']) && $_GET['login'] == 'user') || isset($_POST['user_login'])) {
					$PTMPL['user_login'] = ' checked';
					$login = $framework->authenticateUser();
				} else {
					$login = $framework->administrator(1);
				}
				if (isset($login['username']) && $login['username'] == $username) {
					$notice = messageNotice('Login Successful', 1);
					if ((isset($_GET['login']) && $_GET['login'] == 'user') || isset($_POST['user_login'])) {
						$framework->redirect(cleanUrls('profile'));
					} else {
						$framework->redirect(cleanUrls('moderate'));
					}
				} else {
					$notice = messageNotice($login, 3);
				}
				$PTMPL['notification'] = $notice; 
			}
			$theme = new themer('admin/admin_login');
			$PTMPL['content'] = $theme->make();
		} else {
        	$PTMPL['page_title'] = 'Error 403';
			$PTMPL['content'] = notAvailable('', '', 403);
		}
		// Set the active landing page_title 
		$theme = new themer('admin/fullpage');
	}
		// $data = 'deserunt {$texp->davidson} in';
		// echo $t = $framework->auto_template($data, 1);
	return $theme->make();
}
?>
