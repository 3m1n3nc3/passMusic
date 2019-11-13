<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $admin, $user, $user_role, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = 'Account'; 
	
	$PTMPL['site_url'] = $SETT['url']; 

    $mod = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=new_release');
    $adn = cleanUrls($SETT['url'] . '/index.php?page=admin');

    $link_a = array('' => 'Account', 'update' => 'Update Account', 'messages' => 'Messages', 'notifications' => 'Notifications');
    $side_links = '';
    foreach ($link_a as $url => $title) {  
        $active = $url == (isset($_GET['view']) ? $_GET['view'] : '') ? ' active' : '';
 
        $link = cleanUrls($SETT['url'].'/index.php?page=account'.($url != '' ? '&view='.$url : ''));  
        $side_links .= '<a href="'.$link.'" class="btn btn-info btn-block'.$active.'" for="upload_photo">'.$title.'</a>'; 
    }

    $PTMPL['side_links'] = $side_links;
    $PTMPL['side_links'] .= $user_role > 2 ? '<a href="'.$mod.'" class="btn btn-info btn-block" for="upload_photo">New Release</a>' : '';
    $PTMPL['side_links'] .= $admin ? '<a href="'.$adn.'" class="btn btn-info btn-block" for="upload_photo">Admin</a>' : '';

	if ($user) { 
        $PTMPL['page_title'] = ucfirst($user['username']); 
        $PTMPL['page_titler'] = 'Your profile'; 

        $PTMPL['profile_photo'] = getImage($user['photo'], 1, 1);
        $PTMPL['cover_photo'] = (($user['cover']) && ($user['photo'])) ? getImage($user['cover'], 1, 1) : getImage($user['photo'], 1, 1);
        $PTMPL['verified'] = $user['verified'] ? ' is-verified' : '';

        $PTMPL['secondary_navigation'] = secondaryNavigation($user['uid']);

        // Show the upload profile photo modal
        $upt = new themer('account/upload_profile_photo'); 
        $uct = new themer('account/upload_cover_photo'); 
        $PTMPL['upload_photo_modal'] = modal('uploadPhoto', $upt->make(), $LANG['upload_profile_photo'], 1);
        $PTMPL['upload_photo_modal'] .= modal('uploadCover', $uct->make(), $LANG['upload_cover_photo'], 1);

        $PTMPL['photo'] = getImage($user['photo'], 1);
        $PTMPL['fname'] = ucfirst(isset($_POST['firstname']) ? $_POST['firstname'] : $user['fname']);
        $PTMPL['lname'] = ucfirst(isset($_POST['lastname']) ? $_POST['lastname'] : $user['lname']);
        $PTMPL['email'] = isset($_POST['email']) ? $_POST['email'] : $user['email'];
        $PTMPL['username'] = isset($_POST['username']) ? $_POST['username'] : $user['username'];
        $PTMPL['label'] = ucfirst(isset($_POST['label']) ? $_POST['label'] : $user['label']);
        $PTMPL['country'] = ucfirst(isset($_POST['country']) ? $_POST['country'] : $user['country']);
        $PTMPL['state'] = ucfirst(isset($_POST['state']) ? $_POST['state'] : $user['state']);
        $PTMPL['city'] = ucfirst(isset($_POST['city']) ? $_POST['city'] : $user['city']);

        $PTMPL['show_label'] = $user_role > 1 ? '
        <div class="col-md-12 mb-4">
            <label>Record Label</label>
            <div class="font-weight-bold mb-4">'.$PTMPL['label'].'</div> 
        </div>
        ' : '';

        $PTMPL['intro'] = ucfirst(isset($_POST['intro']) ? $_POST['intro'] : $user['intro']);
        $PTMPL['facebook'] = isset($_POST['facebook']) ? $_POST['facebook'] : $user['facebook'];
        $PTMPL['twitter'] = isset($_POST['twitter']) ? $_POST['twitter'] : $user['twitter'];
        $PTMPL['instagram'] = isset($_POST['instagram']) ? $_POST['instagram'] : $user['instagram'];
        $PTMPL['newsletter_check'] = isset($_POST['newsletter']) || $user['newsletter'] ? ' checked' : '';

        $PTMPL['set_country'] = set_local(1, isset($_POST['country']) ? $_POST['country'] : $user['country']);
        $PTMPL['set_state'] = '<option value="'.$user['state'].'" selected>'.$user['state'].'</option>';
        $PTMPL['set_city'] = '<option value="'.$user['city'].'" selected>'.$user['city'].'</option>';

        $PTMPL['show_label_form'] = $user_role > 1 ? '
            <label for="label">Record Label</label>
            <input type="text" id="label" class="form-control mb-4" name="label" placeholder="Record Label" value="'.$PTMPL['label'].'"> 
        ' : '';

        if (isset($_GET['view'])) { 
            if ($_GET['view'] == 'update') {
                $theme = new themer('account/update');

                $PTMPL['page_title'] = 'Update Profile'; 

                if (isset($_POST['update'])) {
                    $framework->firstname = $framework->db_prepare_input($_POST['firstname']);
                    $framework->lastname = $framework->db_prepare_input($_POST['lastname']);
                    $framework->username = $framework->db_prepare_input($_POST['username']);
                    $framework->email = $framework->db_prepare_input($_POST['email']);
                    $framework->label = isset($_POST['label']) ? $framework->db_prepare_input($_POST['label']) : '';
                    $framework->intro = $framework->db_prepare_input($_POST['intro']);
                    $framework->facebook = $framework->db_prepare_input($_POST['facebook']);
                    $framework->twitter = $framework->db_prepare_input($_POST['twitter']);
                    $framework->instagram = $framework->db_prepare_input($_POST['instagram']);
                    $framework->country = $framework->db_prepare_input($_POST['country']);
                    $framework->state = $framework->db_prepare_input($_POST['state']);
                    $framework->city = $framework->db_prepare_input($_POST['city']);
                    $framework->newsletter = isset($_POST['newsletter']) && $_POST['newsletter'] == 'on' ? 1 : 0;
                    $PTMPL['notification'] = $framework->updateProfile();
                }

            } elseif ($_GET['view'] == 'notifications') {
                $theme = new themer('account/notifications'); 

                $PTMPL['page_title'] = 'Notifications'; 
                
                $notifications = $databaseCL->fetchNotifications();
                if ($notifications) {
                    $notification_list = '';
                    foreach ($notifications as $new) {
                        $databaseCL->track = $new['object'];

                        if ($new['type'] == 1 || $new['type'] == 3) {
                            $track = $databaseCL->fetchTracks(null, 2);
                        } else {

                        }
                        $track = ($new['type'] == 1 ? $databaseCL->fetchTracks(null, 2) : $databaseCL->fetchAlbum($new['object']))[0];

                        $by = $framework->userData($new['by'], 1);
                        $by_profile = '<a href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$by['username']).'">'.ucfirst($by['username']).'</a>';
                        if ($new['type'] == 0) {
                            $icon = 'users';
                            $icon_color = ' text-info';
                            $action = sprintf($LANG['follow_notice'], $by_profile);
                        } elseif ($new['type'] == 1 || $new['type'] == 2) {
                            $icon = 'heart';
                            $icon_color = ' text-danger';
                            $sub = $new['type'] == 1 ? $LANG['track'] : $LANG['album'];
                            $action = sprintf($LANG['liked_notice'], $by_profile, $sub, ucfirst($track['title']));
                        } elseif ($new['type'] == 3 || $new['type'] == 4) {
                            $action = sprintf($LANG['comment_notice'], $by_profile);
                        } else {

                        }
                        $notification_list .= '
                        <div class="project_details mb-3">
                            <div class="project_details__text text-justify">
                                '.$action.'
                                <br><b><i class="fa '.icon(3, $icon).$icon_color.'"></i> '.$marxTime->timeAgo($new['date']).'</b> 
                            </div>
                        </div>';
                    }
                    $PTMPL['notification_list'] = $notification_list;
                }
            }
        } else {
            $theme = new themer('account/account'); 
        }
            
        $PTMPL['content'] = $theme->make();
    }
	// Set the active landing page_title 
	$theme = new themer('account/container');
	return $theme->make();
}
// Send notifications from
// Follow
// Track Likes
// Album Likes
// Track Comments
// Album Comments
// Admin
?>


