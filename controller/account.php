<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $configuration, $admin, $user, $user_role, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = 'Account'; 
	
	$PTMPL['site_url'] = $SETT['url'];
    $messaging = new social;

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
        $PTMPL['user_username_url'] = cleanUrls($SETT['url'].'/index.php?page=artist&artist=').'<span id="repusr">'.$PTMPL['username'].'</span>';  

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

                $PTMPL['notification_list'] = showNotifications();
            } elseif ($_GET['view'] == 'messages') {
                $theme = new themer('account/messages'); 

                $PTMPL['page_title'] = 'Messages'; 

                $thread = isset($_GET['thread']) ? $_GET['thread'] : '';
                $treader = $user['uid'];
                $message_thread = $framework->dbProcessor("SELECT * FROM messenger WHERE `thread` = '$thread' AND `thread` != '' AND `receiver` != '$treader'", 1)[0];
                if ($message_thread) {
                    $message_reciever = $message_thread['receiver'];
                    $message_sender = $message_thread['sender'];            
                } else {
                    $message_reciever = isset($_GET['r_id']) ? $_GET['r_id'] : '';
                    $message_sender = isset($_GET['user_id']) ? $_GET['user_id'] : $user['uid'];
                }

                // Fetch the message
                if (isset($_GET['mid']) || isset($_GET['thread']) || isset($_GET['r_id'])) {
                    $PTMPL['messages'] = $messaging->messenger_master($message_sender, $message_reciever);

                    // Fetch the followers
                    // $social->active = $_GET['id'];      
                } else {
                    // Show ads if user id is not set
                    $PTMPL['messages'] = '<div class="mt-3 m-2">Start a chat</div>';
                }  
                // $social->onlineTime = $settings['online_time']; 
                $PTMPL['follows'] = $messaging->activeChats($user['uid'], 0);         
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


