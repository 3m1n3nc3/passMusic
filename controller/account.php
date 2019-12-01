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

    $allow_admin_login = isset($_GET['login']) && $_GET['login'] == 'admin' && !$admin ? 1 : 0;
    $full_container = '';
	if ($user && !$allow_admin_login) { 
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


        // Set the seo tags
        $PTMPL['seo_meta_plugin'] = seo_plugin($user['photo'], $PTMPL['page_title'], $PTMPL['intro']);

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

            } 
            elseif ($_GET['view'] == 'notifications') {
                $theme = new themer('account/notifications'); 

                $PTMPL['page_title'] = 'Notifications'; 

                $PTMPL['notification_list'] = showNotifications(); 

            } 
            elseif ($_GET['view'] == 'messages') {
                $theme = new themer('account/messages'); 

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

                if (isset($_SESSION['group_thread'])) {
                    $project = $databaseCL->fetchProject($message_reciever)[0];
                    $page_title = $project ? ' | Project ' . $project['title'] : '';
                } else {
                    $rcvr = $framework->userData($message_reciever, 1);
                    $page_title = $rcvr ? ' | '.$framework->realName($rcvr['username'], $rcvr['fname'], $rcvr['lname']) : ''; 
                }

                $PTMPL['page_title'] = 'Messages' . $page_title; 

                // Fetch the message
                if (isset($_GET['cid']) || isset($_GET['thread']) || isset($_GET['r_id'])) {
                    $messaging->thread = isset($_GET['thread']) ? $_GET['thread'] : '';
                    if (isset($_GET['thread'])) {
                        str_ireplace('grpc', '', $_GET['thread'], $is_group);
                        if ($is_group) {
                            $_SESSION['group_thread'] = $_GET['thread'];
                        } else {
                            if (isset($_SESSION['group_thread'])) {
                                unset($_SESSION['group_thread']);
                            }                            
                        }
                    } else {
                        if (isset($_SESSION['group_thread'])) {
                            unset($_SESSION['group_thread']);
                        }                        
                    }
                    $PTMPL['messages'] = $messaging->messenger_master($message_sender, $message_reciever);
                } else {
                    // Show ads if user id is not set
                    $PTMPL['messages'] = '<div class="mt-3 m-2 message-error">'.$LANG['start_a_message'].'</div>';
                }  
                // $social->onlineTime = $settings['online_time']; 
                $PTMPL['follows'] = $messaging->activeChats($user['uid'], 1);         
                $PTMPL['recent_chats'] = $messaging->activeChats($user['uid'], 0);   

                // Set the seo tags
                $PTMPL['seo_meta_plugin'] = seo_plugin(null, $PTMPL['page_title']);      
            } else {
                $theme = new themer('account/account'); 
            }
        } else {
            $theme = new themer('account/account'); 
        }
    } else { 
        $theme = new themer('account/login');
        // Set the active landing page_title 
        $full_container = '-fullpage';
        $PTMPL['page_title'] = 'Login'; 


        if (isset($_POST['login']) || isset($_POST['register'])) {
            $PTMPL['username'] = $username = isset($_POST['username']) ? $framework->db_prepare_input($_POST['username']) : '';
            $PTMPL['password'] = $password = isset($_POST['password']) ? $framework->db_prepare_input($_POST['password']) : '';
            $PTMPL['email'] = $email = isset($_POST['email']) ? $framework->db_prepare_input($_POST['email']) : '';
            $recaptcha2x = isset($_POST['recaptcha']) ? $framework->db_prepare_input($_POST['recaptcha']) : null;

            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $PTMPL['remember'] = ' checked';
                $framework->remember = 1;
            }
            if (isset($_POST['newsletter']) && $_POST['newsletter'] == 'on') {
                $PTMPL['newsletter'] = ' checked';
                $framework->newsletter = 1;
            } else {
                $framework->newsletter = 0;
            }

            $framework->username = $username;
            $framework->email = $email;
            $framework->password = hash('md5', $password); 

            if (isset($_GET['login']) && $_GET['login'] == 'admin') {
                $login = $framework->administrator(1);
                $notice = messageNotice($login, 3, 2);
            } elseif (isset($_GET['login']) && $_GET['login'] == 'register') {
                $ver_user = $framework->userData($username, 1);
                if (!$framework->captchaVal($recaptcha2x)) {
                    $reg = messageNotice($LANG['invalid_capthca'], 0, 2);
                }
                elseif (mb_strlen($username) < 5) {
                    $reg = messageNotice($LANG['username_short'], 0, 2);
                }
                elseif ($username == $ver_user['username']) {
                    $reg = messageNotice($LANG['username_used'], 0, 2);
                }  
                elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $reg = messageNotice($LANG['invalid_email'], 0, 2);   
                } 
                elseif ($email == $framework->checkEmail($email)) {
                    $reg = messageNotice($LANG['email_used'], 0, 2);
                } 
                elseif (mb_strlen($password) < 8) {
                    $reg = messageNotice($LANG['password_short'], 0, 2);
                }
                else {
                    $reg = $framework->registrationCall();
                    $framework->redirect(cleanUrls('account'));
                }
                $notice = $reg;
            } else {
                $login = $framework->authenticateUser();
                $notice = messageNotice($login, 3, 2);
            }
            if (isset($login['username']) && $login['username'] == $username) {
                $notice = messageNotice('Login Successful', 1, 2);

                // Save the referrer session to a new session
                if (isset($_SESSION['referrer'])) { 
                    $_SESSION['temp_referrer'] = urlrecoder($_SESSION['referrer'], 1);
                }

                if (isset($_GET['login']) && $_GET['login'] == 'admin') {
                    if (isset($_SESSION['referrer'])) {
                        unset($_SESSION['referrer']);
                        $framework->redirect(cleanUrls($_SESSION['temp_referrer']), 1);
                    } else {
                        $framework->redirect(cleanUrls('admin'));
                    }
                } else {
                    if (isset($_SESSION['referrer'])) {
                        unset($_SESSION['referrer']);
                        $framework->redirect(cleanUrls($_SESSION['temp_referrer']), 1);
                    } else {
                        $framework->redirect(cleanUrls('account'));
                    }
                    $framework->redirect(cleanUrls('account'));
                }
            } else {
                $notice = $notice;
            }
            $PTMPL['notification'] = $notice; 

            // Set the seo tags
            $PTMPL['seo_meta_plugin'] = seo_plugin(null, $PTMPL['page_title']);
        }

        if (isset($_GET['view']) && $_GET['view'] == 'access') {
            if (isset($_GET['login'])) {
                // Show the login menu
                if ($_GET['login'] == 'register') {
                    $theme = new themer('account/register');
                    $PTMPL['page_title'] = 'Register'; 
                    $PTMPL['recaptcha'] = extra_fields()['recaptcha'];
                } else {
                    $theme = new themer('account/login');
                }
                $PTMPL['page_title'] = $_GET['login'] == 'admin' ? 'Admin Login' : $PTMPL['page_title'];
            }  

            // Set the seo tags
            $PTMPL['seo_meta_plugin'] = seo_plugin(null, $PTMPL['page_title']);
        }
    } 
 
    $PTMPL['content'] = $theme->make();

    $theme = new themer('account/container'.$full_container); 
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


