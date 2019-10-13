<?php 

use wapmorgan\Mp3Info\Mp3Info;

function messageNotice($str, $type = null, $size = '3', $iS = '2') {
    switch ($type) {
        case 1:
            $alert = 'success';
            $i = 'check-circle';
            break;

        case 2:
            $alert = 'warning';
            $i = 'question-circle';
            break;

        case 3:
            $alert = 'danger';
            $i = 'times-circle';
            break;

        default:
            $alert = 'info';
            $i = 'exclamation-circle';
            break;
    }
    $string = '
    <div class="p-2 mx-1 alert alert-' . $alert . '"> 
        <div class="d-flex">
            <i class="pr-4 fa fa-'.$iS.'x fa-'.$i.'"></i>
            <div class="flex-grow-1"><h'.$size.' class="text-center font-weight-bolder" style="margin-bottom: 0px;">' . $str . '</h'.$size.'></div>
        </div>
    </div>';
    return $string;
}

function bigNotice($str, $type = null, $alt = null) {
    switch ($type) {
        case 1:
            $alert = 'success';
            $i = 'check-circle';
            break;

        case 2:
            $alert = 'warning';
            $i = 'question-circle';
            break;

        case 3:
            $alert = 'danger';
            $i = 'times-circle';
            break;

        default:
            $alert = 'info';
            $i = 'exclamation-circle';
            break;
    }
    if ($alt) {
        $extra = $alt;
    } else {
        $extra = 'bg-light';
    }
    $string = '
    <div class="h1 d-flex text-'.$alert.' p-4 m-4 '.$extra.' rounded border border-'.$alert.'"> 
        <i class="pr-4 fa fa-'.$i.'"></i> 
        <div class="flex-grow-1"><div class="text-center">' . $str . '</div></div>
    </div>';
    return $string;
}

function seo_plugin($image, $twitter, $facebook, $desc, $title) {
    global $SETT, $PTMPL, $configuration, $site_image;

    $twitter = ($twitter) ? $twitter : $configuration['site_name'];
    $facebook = ($facebook) ? $facebook : $configuration['site_name'];
    $title = ($title) ? $title . ' ' : '';
    $titles = $title . 'On ' . $configuration['site_name'];
    $image = ($image) ? $image : $site_image;
    $alt = ($title) ? $title : $titles;
    $desc = rip_tags(strip_tags(stripslashes($desc)));
    $desc = strip_tags(myTruncate($desc, 350));
    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $plugin = '
    <meta name="description" content="' . $desc . '"/>
    <link rel="canonical" href="' . $url . '" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="' . $titles . '" />
    <meta property="og:url" content="' . $url . '"/>
    <meta property="og:description" content="' . $desc . '" />
    <meta property="og:site_name" content="' . $configuration['site_name'] . '" />
    <meta property="article:publisher" content="https://www.facebook.com/' . $configuration['site_name'] . '" />
    <meta property="article:author" content="https://www.facebook.com/' . $facebook . '" />
    <meta property="og:image" content="' . $image . '" />
    <meta property="og:image:secure_url" content="' . $image . '" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="628" />
    <meta property="og:image:alt" content="' . $alt . '" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:description" content="' . $desc . '" />
    <meta name="twitter:title" content="' . $titles . '" />
    <meta name="twitter:site" content="@' . $configuration['site_name'] . '" />
    <meta name="twitter:image" content="' . $image . '" />
    <meta name="twitter:creator" content="@' . $twitter . '" />';
    return $plugin;
}

function getLocale($type = null, $id = null) {
    // $framework->
    global $framework;
    if ($type == 1) {
        $sql = sprintf("SELECT * FROM " . TABLE_CITIES . " WHERE state_id = '%s'", $id);
    } elseif ($type == 2) {
        $sql = sprintf("SELECT * FROM " . TABLE_STATES . " WHERE country_id = '%s'", $id);
    } else {
        $sql = sprintf("SELECT * FROM " . TABLE_COUNTRIES);
    }
    if ($type == 3) {
        $list = getLocale();
        $listed = '';
        foreach ($list as $name) {
            if ($id == $name['name']) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $listed .= '<option id="' . $name['id'] . '" value="' . $name['name'] . '"' . $selected . '>' . $name['name'] . '</option>';
        }
        return $listed;
    } else {
        return $framework->dbProcessor($sql, 1);
    }
}

function fileInfo($file, $type = null) {
    $getID3 = new getID3;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $source = $file;
    if ($ext == 'mp3' || $ext == 'wav') {
        $newFile = getAudio($source, 1);
    } else {
        $newFile = getFiles($source, 1);
    }
    if (file_exists($newFile) && is_file($newFile)) {
        if ($type) {
            // Use Mp3Info to get audio tags
            $audio_tags = new Mp3Info($newFile);
            return $duration = floor($audio_tags->duration / 60).':'.floor($audio_tags->duration % 60);
        } else {
            // Use getID3 to get file info
            $FileInfo = $getID3->analyze($newFile);
            return $FileInfo;
        }
    }
    return;
}

function getImage($image, $type = null) {
    // $a = 1: Get direct link to image
    global $SETT, $framework;
    if (!$image) {
      $dir = $SETT['url'] . '/uploads/img/';
      $image = 'default.png';
    }

    $c = null;
    if ($type == 1 || $type == 3) {
      // Uploaded images
      $dir_url = $SETT['url'] . '/uploads/photos/';
      $_dir = $SETT['working_dir'].'/uploads/photos/';
      $c = 1;
    } elseif ($type == 2) {
      // More Site specific images
      $dir_url = $SETT['url'] . '/' . $SETT['template_url'] . '/images/';
      $_dir = $SETT['template_url'] . '/images/';
    } else {
      // Site specific images
      $dir_url = $SETT['url'] . '/' . $SETT['template_url'] . '/img/';
      $_dir = $SETT['template_url'] . '/img/';
    } 

    // Show the image
    if ($framework->trueAjax()) {
        if (file_exists($_dir.$image) && is_file($_dir.$image)) {
          $image = $dir_url.$image;
        } else {
          $image = $SETT['url'] . '/uploads/photos/default.png';
        }
    } elseif ($type == 3)  {
        $image = $dir_url.$image;
        if (@exif_imagetype($image)) {
          $image = $image;
        } else {
            $image = $SETT['url'] . '/uploads/photos/default.png';
        }
    } else {
        if (file_exists($_dir.$image) && is_file($_dir.$image)) {
          $image = $dir_url.$image;
        } else {
          $image = $SETT['url'] . '/uploads/photos/default.png';
        }
    } 
    return $image;
}

function getVideo($source) {
    global $SETT, $framework;
    $link = $framework->determineLink($source);

    if (!$source) {
        $source = 'defaultvid.png';
        return $source = $SETT['url'] . '/uploads/videos/' . $source;
    }
    if ($link) {
        $source = $link;
    } else {
        $source = $SETT['url'] . '/uploads/videos/' . $source;
    }
    return $source;
} 

function getAudio($source, $t=null) {
    global $SETT, $framework;   

    $_source = $SETT['working_dir'].'/uploads/audio/' . $source;
    if (file_exists($_source) && is_file($_source)) {
        if ($t) {
            return $_source;    
        }
        return $source = $SETT['url'] . '/uploads/audio/' . $source;    
    }
    return;
} 

function getFiles($source, $t=null) {
    global $SETT, $framework;   

    $_source = $SETT['working_dir'].'/uploads/files/' . $source;
    if (file_exists($_source) && is_file($_source)) {
        if ($t) {
            return $_source;    
        }
        return $source = $SETT['url'] . '/uploads/files/' . $source;    
    }
    return;
} 

/**
 * /* This function will convert your urls into cleaner urls
 **/
function cleanUrls($url) {
    global $configuration; //$configuration['cleanurl'] = 1;
    if ($configuration['cleanurl']) {
        $pager['homepage'] = 'index.php?page=homepage';
        $pager['news'] = 'index.php?page=news';
        $pager['trainings'] = 'index.php?page=trainings';

        if (strpos($url, $pager['homepage'])) {
            $url = str_replace(array($pager['homepage'], '&user=', '&read='), array('homepage', '/', '/'), $url);
        } elseif (strpos($url, $pager['news'])) {
            $url = str_replace(array($pager['news'], '&read=', '&id'), array('news', '/', '/'), $url);
        } elseif (strpos($url, $pager['trainings'])) {
            $url = str_replace(array($pager['trainings'], '&view=', '&id'), array('trainings', '/', '/'), $url);
        }
    }
    return $url;
}
 
function accountAccess($type = null) {
    global $LANG, $PTMPL, $SETT, $settings;
    if ($type == 0) {
        $theme = new themer('homepage/signup');
        $footer = '';
    } else {
        $theme = new themer('homepage/login');
        $footer = '';
    }

    $OLD_THEME = $PTMPL;
    $PTMPL = array();

    $PTMPL['register_link'] = cleanUrls($SETT['url'] . '/index.php?page=account&register=true');


    $footer = $theme->make();
    $PTMPL = $OLD_THEME;
    unset($OLD_THEME);
    return $footer;
}

function manageButtons($type = null, $cid = null, $mid = null) {
    global $user_role, $user, $framework, $SETT;
    $link = '';

    if ($type == 0) {
        // Edit Module
        $link = cleanUrls($SETT['url'] . '/index.php?page=training&module=edit&moduleid=' . $mid);
    } elseif ($type == 1) {
        // Edit Course
        $link = cleanUrls($SETT['url'] . '/index.php?page=training&course=edit&courseid=' . $cid);
    } elseif ($type == 2) {
        $link = cleanUrls($SETT['url'] . '/index.php?page=training&module=add');
    } elseif ($type == 3) {
        $link = cleanUrls($SETT['url'] . '/index.php?page=training&course=add');
    }
    return $link;
}

function secureButtons($class, $title, $type, $cid, $mid, $x = null) {
    global $user, $user_role;
    $link = manageButtons($type, $cid, $mid);
    $gcrs = getCourses(1, $cid)[0];
    $gmd = getModules(2, $mid)[0];

    $class = $class ? ' ' . $class : '';
    $btnClass = $x ? '' : 'btn';
    $_btn = '';
    $btn = '<a href="' . $link . '" class="' . $btnClass . $class . '">' . $title . '</a>';
    $allow = 0;

    if ($type == 0) {
        // Edit Module
        if ($gmd['creator_id'] == $user['id']) {
            $allow = 1;
        }
    } elseif ($type == 1) {
        // Edit Course
        if ($gcrs['creator_id'] == $user['id']) {
            $allow = 1;
        }
    }
    if ($allow == 1 && ($type == 0 || $type == 1)) {
        $btn = $btn;
    } elseif ($user_role >= 2 && ($type == 0 || $type == 1)) {
        $btn = $btn;
    } elseif ($user_role >= 1 && ($type == 2 || $type == 3)) {
        $btn = $btn;
    } else {
        $btn = $_btn;
    }

    return $btn;
}

function simpleButtons($class, $title, $link, $x = null) {
    global $user, $user_role;

    $class = $class ? ' ' . $class : '';
    $btnClass = $x ? '' : 'btn';
    $btn = '<a href="' . $link . '" class="' . $btnClass . $class . '">' . $title . '</a>';

    return $btn;
}

/**
 * [deleteFile description]
 * @param  variable $name is the full qualified name including extension of the file to be deleted
 * @param  variable $type describes what is to be deleted; 0 or null for audio, 1 for photo, 2 for other files
 * @param  [variable $fb is used as fallback when an ajax xhr request type is not possible for a ajax request
 * @return integer       1 if successful of 0 if failed
 */
function deleteFile($name, $type = null, $fb = null) {
    global $SETT, $framework;
 
    if ($type == 1) {
        $path = 'photos';
    } elseif ($type == 2) {
        $path = 'files';
    } else {
        $path = 'audio';
    } 
    $fallback = $SETT['working_dir'] . '/uploads/' . $path . '/' . $name; 

    if ($framework->trueAjax() || $fb) {
        $file =  '../uploads/' . $path . '/' . $name;
    } else {
        $file =  getcwd() . '/uploads/' . $path . '/' . $name;
    }  

    if ($name !== 'default.png') {
        if (file_exists($file) && is_file($file)) {  
            clearstatcache();
            return unlink($file); 
        } elseif (file_exists($fallback) && is_file($fallback)) { 
            clearstatcache();
            return unlink($fallback);  
        } 
    }
    return 0;
}

function notAvailable($string, $pad='', $type = null) {
    if ($type == 1) {
        $return = 
        '<div class="tracker trackless song-container text-center">
            <div class="'.$pad.'pad-section">  
                <i class="fa fa-cloud-download"></i>
                <p class="small para">' . $string . '</p> 
            </div>
        </div>';
    } else {
        if ($pad == '') {
            $pad = 'display-1';
        }
        $return = 
        '<div class="text-center">
            <div class="p-4 m-4 border rounded border-info bg-light text-info">
                <i class="'.$pad.' ion-ios-help-circle-outline"></i>
                <p class="'.$pad.'">'.$string.'</p>
            </div>
        </div>';
    }
    return $return;
} 

function restrictedContent($content, $tab = null) {
    global $LANG, $SETT, $PTMPL, $contact_, $configuration, $framework, $user, $user_role;
    if (isset($tab)) {
        if ($content == 1) {
            $theme = new themer('project/restricted_tabs'); $section = '';
        }
    } else {
        if ($content == 1) {
            $theme = new themer('project/restricted_tabs_content'); $section = '';
        } elseif ($content == 2) {
            $theme = new themer('project/display_project_stems'); $section = '';
        } elseif ($content == 3) {
            $theme = new themer('project/display_manage_buttons'); $section = '';
        }
    }

    $section = $theme->make();
    return $section;
}

function globalTemplate($type) {
    global $LANG, $SETT, $PTMPL, $contact_, $configuration, $framework, $user, $user_role;
    if ($type == 1) {
        $theme = new themer('homepage/header'); $section = '';
    } elseif ($type == 2) {
        $theme = new themer('homepage/player'); $section = '';
    } elseif ($type == 3) {
        $theme = new themer('homepage/sidebar'); $section = '';
    } elseif ($type == 4) {
        $theme = new themer('homepage/right_sidebar'); $section = '';
    } else {
        $theme = new themer('container/footer'); $section = '';
    }
    $login_link = cleanUrls($SETT['url'] . '/index.php?page=account&process=login');
    $PTMPL['dashboard_url'] = cleanUrls($SETT['url'] . '/index.php?page=homepage');
    $PTMPL['user_url'] = $user_url = cleanUrls($SETT['url'] . '/index.php?page=account&profile=home');
    $PTMPL['username_url'] = simpleButtons('logout', 'Account', $user_url, 1);
    $PTMPL['site_title_'] = ucfirst($configuration['site_name']);
    // $PTMPL['copyright'] = '&copy; ' . ucfirst($LANG['copyright']) . ' ' . date('Y') . ' ' . $contact_['c_line'];
    $PTMPL['logout_url'] = cleanUrls($SETT['url'] . '/index.php?page=homepage&logout=true');

    if ($user_role >=3) {
      $management = cleanUrls($SETT['url'] . '/index.php?page=management');
      $PTMPL['management'] = simpleButtons("bordered background_green2", 'Manage Site <i class="fa fa-cog"></i>', $management);
    }

    $avatar_division = '
      <div class="top_avatar">
        <div class="user_avatar">
          <a data-toggle="collapse" href="#logout" title="edit profile">
            <img alt="' . ucfirst($user['username']) . ' avatar" src="' . getImage($user['photo'], 1) . '">
          </a>
        </div>
        <div class="user_name">
          ' . ucfirst($user['username']) . '
        </div>
      </div>';
    $login_division = '<div class="">' . simpleButtons("bordered background_green2", 'Login', $login_link) . ' </div > ';

    $PTMPL['action_btn_link_avatar'] = $user ? $avatar_division : $login_division;
    $section = $theme->make();
    return $section;
} 

function superGlobalTemplate($type=null) {
    global $LANG, $SETT, $PTMPL, $contact_, $configuration, $framework, $user, $user_role;
    if ($type == 1) {
        $theme = new themer('distribution/global/header'); $section = '';
    } else {
        $theme = new themer('distribution/global/footer'); $section = '';
    }
    $login_link = cleanUrls($SETT['url'] . '/index.php?page=account&process=login');
    $PTMPL['artists_services'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=artist-services');
    $PTMPL['sales_report'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=sales-report');
    $PTMPL['user_url'] = $user_url = cleanUrls($SETT['url'] . '/index.php?page=account&profile=home');
    $PTMPL['username_url'] = simpleButtons('logout', 'Account', $user_url, 1);
    $PTMPL['site_title_'] = ucfirst($configuration['site_name']);
    // $PTMPL['copyright'] = '&copy; ' . ucfirst($LANG['copyright']) . ' ' . date('Y') . ' ' . $contact_['c_line'];
    $PTMPL['logout_url'] = cleanUrls($SETT['url'] . '/index.php?page=homepage&logout=true');

    if ($user_role >=3) {
      $management = cleanUrls($SETT['url'] . '/index.php?page=management');
      $PTMPL['management'] = simpleButtons("bordered background_green2", 'Manage Site <i class="fa fa-cog"></i>', $management);
    } 
    $section = $theme->make();
    return $section;
} 

/*
* Generate a modal menu
*/
function modal($modal, $content, $title = null, $size = null, $footer = null, $extra = null) {
    // Always call the extra variable starting with a space
    // Size 1: Small
    // Size 2: Large
    // Size 3: Fluid
 
    if ($size == 1) {
        $size = ' modal-sm';
    } elseif ($size == 2) {
        $size = ' modal-lg';
    } elseif ($size == 3) {
        $size = ' modal-fluid';
    }
    if ($extra == 1) {
      $no_pad = ' style="padding: 0px;"';
    } else {
      $no_pad = '';
      $extra = $extra;
    }
    
    $button = '
    <button type="button" class="close" aria-label="Close" onclick="toggleModal(\''.$modal.'Modal\')">
      <span aria-hidden="true">&times;</span>
    </button>';

    $title = $title ? 
    '<div class="modal-header">
      <h3 class="modal-title font-weight-bold" id="'.$modal.'ModalLabel">'.$title.'</h3>
      '.$button.'
    </div>' : '<div class="modal-header" style="border-bottom: none; padding: 0px;">'.$button.'</div>';

    $footer_content = $footer ? '<div class="modal-footer">'.$footer.'</div>' : '';
    $modal_menu ='
    <div class="modal fade" id="'.$modal.'Modal" tabindex="-1" role="dialog" aria-labelledby="'.$modal.'ModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered'.$size.$extra.'" role="document">
        <div class="modal-content"> 
          '.$title.'  
          <div class="modal-body"'.$no_pad.'>
            '.$content.'
          </div>
            '.$footer_content.'
        </div>
      </div>
    </div>';
    return $modal_menu;
}

function playlistManager($type = null, $track = null) {
    global $SETT, $LANG, $user, $framework, $databaseCL, $marxTime;
    // Type 1: Show modal
    // Type 2: Create playlist form
    // Type 3: Add to playlist form
    if ($type == 1) {
        $modal = modal('playlist', '<div class="modal-container"></div>', '<span id="modal-title">Playlist</span>', 2);
        $content = $modal;
    } elseif ($type == 2) {
        $processor = $SETT['url'].'/connection/uploader.php?action=playlists';
        $form = '
        <form id="create_playlist">
            <div id="save_message"></div> 
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" name="title" placeholder="Title" id="title">
            </div>
            <div class="font-weight-bold d-flex pc-font-2" title="Make this instrumental public">
                <input type="checkbox" name="make_public">
                    <div class="font-weight-bold mx-2 text-info">Public</div>
            </div>
            <div>'.$LANG['public_playlist_notice'].'</div> 
            <button  
                type="button"
                class="btn btn-block btn-info"
                onclick="playlistAction(2, {\'action\': \'create\'})">
                <i class="ion-ios-list"></i> Create Playlist
            </button>            
        </form>';
        $content = $form;
    } elseif ($type == 3) {
        $list = $databaseCL->fetchPlaylist($user['uid'], 1);

        $option = '';
        if ($list) {
            foreach ($list as $row) {
                $option .= '<option value="'.$row['id'].'">'.$row['title'].'</option>';
            }
        }
        $form = '
        <form id="add_to_playlist">
            <div id="save_message"></div> 
            <div class="form-group">
                <label for="title">Select Playlist:</label>
                <select class="form-control" name="playlist">
                    '.$option.'
                </select>
            </div>
            <button  
                type="button"
                class="btn btn-success"
                onclick="playlistAction(2, {\'action\': \'add\', \'track\': \''.$track.'\'})">
                <i class="ion-ios-add-circle-outline"></i> Add to Playlist
            </button>
            <a href="#"
                class="btn btn-info"   
                onclick="playlist_modal(2, {\'action\': \'c_list\'})">
                <i class="ion-ios-list"></i> '.$LANG['create_playlist'].'
            </a> 
        </form>';
        $content = $form;
    }
    return $content;
}

function listTracks($_track, $n, $x=null) {
    global $SETT, $framework, $databaseCL, $marxTime;
    $explicit = $small = '';
    if ($x==1) {
        $small = '_small';
    }
    $duration = '00:00';
    if (getAudio($_track['audio'], 1)) {
        $duration = fileInfo($_track['audio'])['playtime_string'];
    }
    
    if ($_track['explicit']) {
        $explicit = '
            <div class="track__explicit">
              <span class="label">Explicit</span>
            </div>';
    }
    $artist = $framework->userData($_track['artist_id'], 1);
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $t_format = strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION));

    $count_views = $databaseCL->fetchStats(1, $_track['id'])[0]; 
    $count_views = $marxTime->numberFormater($count_views['total']);

    $track = '
    <div class="tracker song-container" id="track'.$_track['id'].'">
        <div class="track__number">'.$n.'</div>
        <div class="track__added subst"> 
            <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="play'.$_track['id'].'" data-track-url="'.getAudio($_track['audio']).'" data-track-format="'.$t_format.'"  data-hideable="0" class="track">
                <div class="tracker__bg_art" style="background-image: url(&quot;'.getImage($_track['art'], 1, 2).'&quot;)">
                    <i class="ion-ios-play" id="icon_play'.$_track['id'].'"></i> 
                    <img style="display: none;" src="'.getImage($_track['art'], 1, 2).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'">
                </div>
            </div>
        </div>
        <div class="track__title'.$small.'">
            <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&id='.$_track['id']).'" id="song-url'.$_track['id'].'"> <div id="song-name'.$_track['id'].'">'.$_track['title'].'</div></a>
        </div>
        <div class="track__author'.$small.'" id="song-author">
            <a href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']).' " id="song-author'.$_track['id'].'"> '.$artist_name.' </a>
        </div>
        <div class="track__download">
            '.showMore_button(1, $_track['id'], '').'
        </div>
        '.$explicit.'
        <div class="track__length">'.$duration.'</div>
        <div class="track__popularity">
            '.$count_views.'
        </div>
    </div> '; 
    return $track;
}

function hiddenPlayer($_track) {
    global $SETT, $user, $framework, $databaseCL;
    $artist = $framework->userData($_track['artist_id'], 1);
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $hidden = '
    <div class="hidden__item" id="track'.$_track['id'].'">
        <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="play'.$_track['id'].'" data-track-url="'.getAudio($_track['audio']).'" data-track-format="'.strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION)).'"  data-hideable="0" class="track">
            <div class="tracker__bg_art" style="background-image: url(&quot;'.getImage($_track['art'], 1, 2).'&quot;)">
                <i class="ion-ios-play" id="icon_play'.$_track['id'].'"></i>
                <img style="display: none;" src="'.getImage($_track['art'], 1, 2).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'">
            </div>
        </div>
        <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&id='.$_track['id']).'" id="song-url'.$_track['id'].'"> <div id="song-name'.$_track['id'].'">'.$_track['title'].'</div></a>
        <a href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']).' " id="song-author'.$_track['id'].'"> '.$artist_name.' </a>
    </div> ';
    return $hidden;
}

function hiddenInfo($_track) {
    global $SETT, $user, $framework, $databaseCL;
    $artist = $framework->userData($_track['artist_id'], 1);
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $hidden = '
    <div class="hidden__item">
        <img style="display: none;" src="'.getImage($_track['art'], 1, 2).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'">
        <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&id='.$_track['id']).'" id="song-url'.$_track['id'].'"> 
        <div id="song-name'.$_track['id'].'">'.$_track['title'].'</div></a>
        <a href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']).' " id="song-author'.$_track['id'].'"> '.$artist_name.' </a>
    </div> ';
    return $hidden;
}

function trackLister($_track, $n, $x=null) {
    global $SETT, $framework, $user, $databaseCL, $marxTime;
    $artist = $framework->userData($_track['artist_id'], 1);
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $t_format = strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION));

    $count_views = $databaseCL->fetchStats(1, $_track['id'])[0]; 
    $count_views = $marxTime->numberFormater($count_views['total']);

    $explicit = $_track['explicit'] ? '
    <div class="track__explicit">
        <span class="label">Explicit</span>
    </div>' : '';

    $list = '
    <div class="song-container" id="track'.$_track['id'].'">
        <div class="trackitem" id="trackitem'.$_track['id'].'">
            <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="play'.$_track['id'].'" data-track-url="'.getAudio($_track['audio']).'" data-track-format="'.$t_format.'"  data-hideable="0" class="track">
                <div class="tracklist__bg_art" style="background-image: url(&quot;'.getImage($_track['art'], 1, 2).'&quot;)">
                    <i class="ion-ios-play" id="icon_play'.$_track['id'].'"></i> 
                    <img style="display: none;" src="'.getImage($_track['art'], 1, 2).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'">
                </div>
            </div>
            <div class="track__added">
                '.clickLike(2, $_track['id'], $user['uid'], 0).'
            </div>
            <div class="track__title">
                <span id="song-author'.$_track['id'].'">'.$artist_name.'</span>
                <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&id='.$_track['id']).'" id="song-url'.$_track['id'].'"> <div id="song-name'.$_track['id'].'">'.$_track['title'].'</div>
            </a>
        </div>
        <div class="track__download">
           '.showMore_button(1, $_track['id'], '').'
        </div>
        '.$explicit.'
        <div class="track__plays"><i class="ion-ios-play"></i> '.$count_views.'</div>
    </div>
    </div> ';
    return $list;
}

function albumsLister($artist, $rows) {
    global $SETT, $user, $framework, $databaseCL, $marxTime;
    $card = '';
    if ($rows) {
        $databaseCL->user_id = $user['uid'];
        $album_id = isset($rows['aid']) ? $rows['aid'] : $rows['id'];

        $get_tracks = $databaseCL->albumEntry($album_id);
        $link = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$rows['safe_link']);
        $like_button = clickLike(1, $album_id, $user['uid']);
        $more_dropdown = showMore_button(2, $album_id, '');

        $n = 0;
        if ($get_tracks) {
            $track = $album = '';
            foreach ($get_tracks as $_track) {
                $n++;
                $track .= listTracks($_track, $n);
            }
            $list_tracks = $track;
        } else {
            $list_tracks = notAvailable('No tracks for this album');
        }

        $card .= '
        <div class="album">
            <div class="album__info">
                <a href="'.$link.'">
                    <div class="album__info__art">
                        <img src="'.getImage($rows['art'], 1, 2).'" alt="When Its Dark Out" />
                    </div>
                </a>
                <div class="album__info__meta">
                    <div class="album__year">'.date('Y', strtotime($rows['release_date'])).'</div>
                    <div class="album__name">
                        <a href="'.$link.'">'.$rows['title'].'</a>
                    </div>
                    <div class="album__actions">
                        '.$like_button.'
                        '.$more_dropdown.'
                    </div>
                </div>
            </div>
            
            <div class="album__tracks">
                <div class="tracks">
                    <div class="tracks__heading">
                        <div class="tracks__heading__number">#</div>
                        <div class="tracks__heading__title">Tracks</div>
                        <div class="tracks__heading__length">
                            <i class="ion-ios-stopwatch"></i>
                        </div>
                        <div class="tracks__heading__popularity">
                            <i class="ion-ios-radio"></i> 
                        </div>
                    </div>
                    '.$list_tracks.'
                </div>
            </div>
        </div>';
    }
    return $card;
}

function artistAlbums($artist) {
    global $SETT, $user, $framework, $databaseCL, $marxTime;
    $albums = $databaseCL->fetchAlbum($artist, 1);

    $card = '';
    if ($albums) {
        foreach ($albums as $rows) {

            $databaseCL->user_id = $user['uid'];
            $get_tracks = $databaseCL->albumEntry($rows['id']);
            $link = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$rows['safe_link']);
            $like_button = clickLike(1, $rows['id'], $user['uid']);
            $more_dropdown = showMore_button(2, $rows['id'], '');

            $n = 0;
            if ($get_tracks) {
                $track = $album = '';
                foreach ($get_tracks as $_track) {
                    $n++;
                    $track .= listTracks($_track, $n);
                }
                $list_tracks = $track;
            } else {
                $list_tracks = notAvailable('No tracks for this album');
            }

            $card .= '
            <div class="album">
                <div class="album__info">
                    <a href="'.$link.'">
                        <div class="album__info__art">
                            <img src="'.getImage($rows['art'], 1).'" alt="When Its Dark Out" />
                        </div>
                    </a>
                    <div class="album__info__meta">
                        <div class="album__year">'.date('Y', strtotime($rows['release_date'])).'</div>
                        <div class="album__name">
                            <a href="'.$link.'">'.$rows['title'].'</a>
                        </div>
                        <div class="album__actions">
                            '.$like_button.'
                            '.$more_dropdown.'
                        </div>
                    </div>
                </div>
                
                <div class="album__tracks">
                    <div class="tracks">
                        <div class="tracks__heading">
                            <div class="tracks__heading__number">#</div>
                            <div class="tracks__heading__title">Tracks</div>
                            <div class="tracks__heading__length">
                                <i class="ion-ios-stopwatch"></i>
                            </div>
                            <div class="tracks__heading__popularity">
                                <i class="ion-ios-radio"></i> 
                            </div>
                        </div>
                        '.$list_tracks.'
                    </div>
                </div>
            </div>';
        }
    }
    return $card;
}

function getTrack($_track, $play_btn=null) {
    if ($play_btn) {
        $play_class = '';
        $play_btn = $play_btn;
    } else {
        $play_class = ' song-play-btn ion-ios-play';
        $play_btn = '';
    } 
    $track = sprintf('
    <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="splay'.$_track['id'].'" data-track-url="'.getAudio($_track['audio']).'" data-track-format="'.strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION)).'" data-hideable="1" class="track%s now-waving">%s</div>', $play_class, $play_btn);
    return $track;
}

function topTracks($type=null, $id=null) {
    global $SETT, $user, $databaseCL, $framework, $marxTime;

    $track_list = $card = $title = $art = $_title = '';

    $_title = 'Top tracks from '; 
    if ($type === 2) {
        $framework->filter = " AND `reg_date` >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)"; 
        $master_list = $framework->userData(NULL, 0);
    } elseif ($type === 3) { 
        $followers = $databaseCL->fetchFollowers($user['uid'])[0]; 
        $databaseCL->username = $followers['username'];
        $databaseCL->fname = $followers['fname'];
        $databaseCL->lname = $followers['lname'];
        $databaseCL->label = $followers['label'];
        $master_list = $databaseCL->fetchRelated($followers['uid'], 1);
    } elseif ($type === 4) {  
        $master_list = $databaseCL->fetchPlaylist(null, 3);
    } else {
        $top = '50 ';
        $_set = $type == 1 ? 'latest' : $_title = 'top';
        $_title = $type == 1 ? 'Latest Hot ' : $_title = 'Top '.$top;
        $master_list = $databaseCL->fetchGenre();
    }
    if ($master_list) {
        foreach ($master_list as $key => $value) { 
            if ($type == 2 || $type == 3) {
                $title = $value['fname'].' '.$value['lname'];
                $title = '<a href="'.cleanUrls($SETT['url'] . '/index.php?page=listen&to=artist&artist='.$value['uid']).'">'.$_title.$title.'</a>';
                $databaseCL->genre = $value['uid'];
            } elseif ($type == 4) {
                $v_user = $framework->userData($value['by'], 1);
                $title = $value['title'].' By '.$v_user['fname'].' '.$v_user['lname'];
                $title = '<a href="'.cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist='.$value['plid']).'">'.$title.'</a>';
                $databaseCL->genre = $value['by'];
                $plst_id = $value['id'];
            } else {
                $title = $_title.$value['title'];
                $title = '<a href="'.cleanUrls($SETT['url'] . '/index.php?page=explore&sets='.$_set.'&go='.urlencode($value['name'])).'">'.$title.'</a>';
                $databaseCL->genre = $value['name'];
            }

            $_topest = $databaseCL->fetchTopTracks($type)[0];
            $top_tracks = $databaseCL->fetchTopTracks($type);
            if ($type == 4) {
                $databaseCL->user_id = $user['uid'];
                $top_tracks = $databaseCL->playlistEntry($plst_id);
                $_topest = $databaseCL->playlistEntry($plst_id)[0];
            } 

            $t_format = strtolower(pathinfo($_topest['audio'], PATHINFO_EXTENSION));
            $art = $type == 2 || $type == 3 ? $value['photo'] : $_topest['art'];

            if ($top_tracks) {
                foreach ($top_tracks as $rows) {
                    $track_list .= hiddenPlayer($rows); 
                }
            }

            $card .= $_topest ? $track_list . '
            <div class="card--content">
                <div class="card--content_image" style="background-image: url(&quot;'.getImage($art , 1).'&quot;);">
                    <i data-track-name="'.$_topest['title'].'" data-track-id="'.$_topest['id'].'" id="play'.$_topest['id'].'" data-track-url="'.getAudio($_topest['audio']).'" data-track-format="'.$t_format.'" data-hideable="1" class="track ion-ios-play-circle">
                    </i>
                </div>
                <div class="section-title card--content__footer"> '.$title.'</div>
            </div>' : null;
        } 
    }

    //$marxTime->dateDifference($_topest['upload_time'], date("Y-m-d H:m:s", strtotime("now")));
    return $card;
} 

function relatedItems($type, $id) {
    global $SETT, $user, $databaseCL, $framework;

    // Check for relates albums 
    $related = null;
    if ($type == 1 || $type == 2) {
        $fetch_album = $databaseCL->fetchAlbum($id)[0];
        $databaseCL->title = $fetch_album['title'];
        $databaseCL->pline = $fetch_album['pline'];
        $databaseCL->cline = $fetch_album['cline']; 
        $databaseCL->tags = $fetch_album['tags'];
    } elseif ($type == 3) {
        // Fetch similar tracks
        $databaseCL->track = $id;
        $track = $databaseCL->fetchTracks(null, 2)[0];

        $databaseCL->title = $track['title'];
        $databaseCL->artist_id = $track['artist_id'];
        $databaseCL->label = $track['label'];
        $databaseCL->pline = $track['pline'];
        $databaseCL->cline = $track['cline'];
        $databaseCL->genre = $track['genre'];
        $databaseCL->tags = $track['tags'];     
    } elseif ($type == 4) {
        // Fetch similar playlists
        $databaseCL->type = 2;
        $databaseCL->user_id = $user['uid'];
        $plst = $databaseCL->fetchPlaylist($id)[0];
        $databaseCL->title = $plst['title'];  
    } elseif ($type == 5) {
        // Fetch similar projects
        $similar = $databaseCL->fetchsimilarProjects($id);
    }

    $sel = 'Undefined Items';
    $card = '';
    if ($type == 1) {
        $sel = 'Albums';
        $related = $databaseCL->fetchRelated($id);
        if ($related) { 
            shuffle($related);
            foreach ($related as $rel) {
                $artist = $framework->userData($rel['by'], 1);
                $link = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$rel['safe_link']);
                $card .= '
                <a href="'.$link.'" class="related-artist">
                  <span class="related-album__img">
                    <img src="'.getImage($rel['art'], 1, 1).'" alt="'.$rel['title'].'" />
                  </span>
                    <span class="related-artist__name">'
                        .$rel['title']
                        .' - <span class="feature-artist">'.$artist['fname'].' '.$artist['lname'].'</span>
                    </span>
                </a>';
            }
        }
    } elseif ($type == 2) {
        $sel = 'Artists';
        $related = $databaseCL->fetchRelated($id, 1);
        if ($related) {
            shuffle($related);
            foreach ($related as $rel) {
                $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$rel['username']);
                $card .= ' 
                <div class="media-card"> 
                     '.smallUser_Card($rel['uid'], null, 1).'
                </div>';
            }
        }
    } elseif ($type == 3) {
        $sel = 'Tracks';
        $related = $databaseCL->fetchRelated($id, 2);
        if ($related) { 
            shuffle($related);
            $card .= '
            <article class="sidebarModule g-all-transitions-200-linear whoToFollowModule" style="display: block;">
                <a class="sidebarHeader g-flex-row-centered-spread pc-link-light refresh-wtf pc-border-light-bottom" href="#">
                    <h3 class="sidebarHeader__title pc-type-light pc-font-tabular g-flex-row-centered ">
                    <i class="ion-ios-disc"></i>
                    <span class="sidebarHeader__actualTitle">SIMILAR TRACKS</span>
                    </h3> 
                </a>  ';
                foreach ($related as $rel) {
                    $artist = $framework->userData($rel['artist_id'], 1);
                    $link = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$rel['safe_link']);
                    $card .= smallTracks_Card($rel['track_id'], 1);
                }
            $card .= '
            </article>';
        }
    } elseif ($type == 4) {
        $sel = 'Playlists';
        $related = $databaseCL->fetchRelated($id, 3);
        if ($related) {
            shuffle($related);
            foreach ($related as $rel) {
                $artist = $framework->userData($rel['by'], 1);
                $link = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist='.$rel['plid']);
                $featured = $databaseCL->playlistEntry($rel['id'], 1)[0];
                $card .= '
                <a href="'.$link.'" class="related-artist">
                    <span class="related-album__img">
                        <img src="'.getImage($featured['art'], 1, 1).'" alt="'.$rel['title'].'" />
                    </span>
                    <span class="related-artist__name">'
                        .$rel['title']
                        .' By <span class="feature-artist">'.$artist['fname'].' '.$artist['lname'].'</span>
                    </span>
                </a>';
            }
        }
    } elseif ($type == 5) {
        $sel = 'Artists';
        $related = $databaseCL->fetchsimilarProjects($id);
        if ($related) {
            shuffle($related);
            foreach ($related as $rel) {
                $link = cleanUrls($SETT['url'] . '/index.php?page=project&project='.$rel['safe_link']);
                $card .= '
                <div class="media-card">
                    <a href="'.$link.'">
                        <div class="media-card__image" style="background-image: url(&quot;'.getImage($rel['cover'], 1, 1).'&quot;);">
                            <i class="ion-ios-open"></i>
                        </div>
                    </a>
                    <a  href="'.$link.'" class="media-card__footer">'.$rel['title'].'</a>
                </div>';
            }
            return $card;
        } else {
            return;
        }
    }  

    return $related ? $card : notAvailable('No similar '.$sel, 'no-');
}

function followCards($rows) {
    global $SETT, $user, $configuration, $databaseCL, $framework;
    // $type = 1: Show Followers
    // $type = 0 or NULL: Show Followings
    //
    $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$rows['username']);    
    $carder = '
        <div class="media-card">
            <a href="%s">
                <div class="media-card__image" style="background-image: url(&quot;%s&quot;);">
                    <i class="ion-ios-open"></i>
                </div>
            </a>
            <a  href="%s" class="media-card__footer">%s %s</a>
        </div>';
    return sprintf($carder, $link, getImage($rows['photo'], 1), $link, $rows['fname'], $rows['lname']);
}

function showTags($str) {
    $string = explode(',',$str);
    $tags = '';
    if ($str) {
        foreach ($string as $list) {
            $tags .= '
              <a href="#"><span class="badge badge-secondary"># '.$list.'</span></a>';
        }
    } else {
        $tags .= '
              <a href="#"><span class="badge badge-secondary"># Music</span></a>';
    }
    return $tags;
}

function artistCard($artist_id) {
    global $SETT, $user, $framework, $databaseCL, $marxTime;
    $artist = $framework->userData($artist_id, 1);
    $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']);
      
    $track_list = $databaseCL->fetchTracks($artist_id, 5);
    $databaseCL->track_list = implode(',', $track_list);
    $fetch_stats = $databaseCL->fetchStats(null)[0];// Type here is null 
    $count_views = $marxTime->numberFormater($fetch_stats['total']);

    $count_albums = $marxTime->numberFormater($databaseCL->fetchAlbum($artist_id, 2)[0]['counter']);

    $followers_link = cleanUrls($SETT['url'] . '/index.php?page=follow&get=followers&artist='.$artist_id);
    $profile_link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']);
    $tracks_link = cleanUrls($SETT['url'] . '/index.php?page=listen&to=artist&artist='.$artist_id);
    $album_link = cleanUrls($SETT['url'] . '/index.php?page=listen&to=artist-album&artist='.$artist_id);

    $follower_count = $databaseCL->fetchFollowers($artist_id, 1)[0]['counter'];
    $count_followers = $marxTime->numberFormater($follower_count); 
    $databaseCL->counter = " AND `public` = '1'";
    $count_tracks = $marxTime->numberFormater($databaseCL->fetchTracks($artist_id, 3)[0]['counter']);

    $follow_btn = clickFollow($artist['uid'], $user['uid']); 
    $card = '
    <div class="artist__card">
        <div class="free-artist">
            <div class="album-artist__">
                <img src="'.getImage($artist['photo'], 1, 1).'" alt="'.$artist['fname'].' '.$artist['lname'].'" />
            </div>
            <a href="'.$link.'">
                <div class="artist__name">'.$artist['fname'].' '.$artist['lname'].'</div>
            </a>
            <div class="artist__followers">
                <a href="'.$followers_link.'" title="'.$count_followers.' Followers" class="mr-3"><i class="ion-ios-people ui"></i> '.$count_followers.'</a>
                <a href="'.$tracks_link.'" title="'.$count_tracks.' Tracks" class="mr-3"><i class="ion-ios-disc ui ml-3"></i> '.$count_tracks.'</a>
                <a href="'.$album_link.'" title="'.$count_albums.' Albums" class="mr-3"><i class="ion-ios-albums  ui ml-3"></i> '.$count_albums.'</a>
                <a href="#" title="'.$count_views.' Views" class="mr-3"> <i class="ion-ios-play ui ml-3"></i> '.$count_views.'</a>
            </div>
            '.$follow_btn.'
        </div>
    </div>';
    return $card;
}

function playlistCard($artist_id) {
    global $SETT, $user, $framework, $databaseCL, $marxTime;
    $artist = $framework->userData($artist_id, 1);

    $playlists = $databaseCL->fetchPlaylist($artist_id, 1);

    $show_playlists = '';
    if ($playlists) {
        foreach ($playlists as $rows) {
            $link = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist='.$rows['plid']);
            $subscribe_btn = clickSubscribe($rows['id'], $user['uid']); 
            $databaseCL->type = 0;
            $featured = $databaseCL->playlistEntry($rows['id'])[0]; 

            $databaseCL->type = 1;
            $count_tracks = $databaseCL->playlistEntry($rows['id'], 1)[0]['track_count'];
            $count_tracks = $marxTime->numberFormater($count_tracks, 1);

            $subcribers_ = $databaseCL->playlistSubscribers($rows['id'])[0]['total'];
            $subcribers_count = $marxTime->numberFormater($subcribers_);
            $subcribers_count_full = $marxTime->numberFormater($subcribers_, 1);
 
            $cards = '
            <div class="artist__card mb-2">
                <div class="free-artist">
                    <div class="album-artist__"> 
                        <a href="'.$link.'">
                            <div class="playlist__image" style="background-image: url(&quot;'.getImage($featured['art'], 1).'&quot;)">
                                <i class="ion-ios-play"></i> 
                            </div>
                        </a>
                    </div>
                    <a href="'.$link.'">
                        <div class="artist__name">'.$rows['title'].'</div>
                    </a>
                    <div class="artist__followers">  
                        <a href="#" title="'.$subcribers_count_full.' Subscribers" class="mr-3"> <i class="ion-ios-people ui ml-3"></i> <span id="subscribers-count-'.$rows['id'].'">'.$subcribers_count.'</span></a>
                        <a href="#" title="'.$count_tracks.' Tracks" class="mr-3"> <i class="ion-ios-disc ui ml-3"></i> '.$count_tracks.'</a>
                    </div>
                    '.$subscribe_btn.' Created by '.$rows['fname'].' '.$rows['lname'].'
                </div>
            </div>';
            $show_playlists .= $cards;
        }
    }
    return $show_playlists;
}

function mostPopular($artist_id, $type=null) {
    global $SETT, $user, $framework, $databaseCL, $marxTime;

    if ($type) {
        $track = $artist_id;
        $userData = $framework->userData($track['artist_id'], 1); 
        $artist_name = $userData['fname'].' '.$userData['lname'];
        $_artist_id = $track['artist_id'];
        $username = $userData['username'];
    } else {
        $track = $databaseCL->fetchTracks($artist_id, 1)[0];
        $artist_name = $track['fname'].' '.$track['lname'];
        $_artist_id = $artist_id;
        $username = $track['username'];
    }
    $likes_display = display_likes_follows(3, $track['id']);
    $explicit = $track['explicit'] ? '<div class="explicit__label ">Explicit</div>' : '';
    $role = $framework->userRoles('', $_artist_id);
    $t_format = strtolower(pathinfo($track['audio'], PATHINFO_EXTENSION));
    $show_likes_count = $databaseCL->LikesCount(2, $track['id']);

    $count_views = $databaseCL->fetchStats(1, $track['id'])[0]; 
    $count_views = $marxTime->numberFormater($count_views['total']);
    $count_likes = $databaseCL->LikesCount(3, $track['id'])[0]; 
    $count_likes = $marxTime->numberFormater($count_likes['total']);

    $track_url = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$track['safe_link'].'&id='.$track['id']);
    $user_url = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$username);

    $card = '
    <style type="text/css">height: 28px;</style>
    <div class="latest-release">
        <div class="popular-card__image" style="background-image: url(&quot;'.getImage($track['art'], 1, 1).'&quot;);">
            <i data-track-name="'.$track['title'].'" data-track-id="'.$track['id'].'" id="play'.$track['id'].'" data-track-url="'.getAudio($track['audio']).'" data-track-format="'.$t_format.'" data-hideable="1" class="track now-waving ion-ios-play-circle">
            </i> 
        </div>
        
        <div class="hidden__item">
            <div class="hidden__item" id="song-name'.$track['id'].'">'.$track['title'].'</div>
            <img class="hidden__item" src="'.getImage($track['art'], 1, 2).'" id="song-art'.$track['id'].'" alt="'.$track['title'].'">
            <a class="hidden__item"href="'.$track_url.'" id="song-url'.$track['id'].'"> <div id="song-name'.$track['id'].'">'.$track['title'].'</div></a>  
            <a class="hidden__item" href="'.$user_url.' " id="song-author'.$track['id'].'"> '.$artist_name.' </a>
        </div>

        <div class="latest-release__song">
            <div class="latest-release__song__title__">
                <a href="'.$user_url.'">'.$artist_name.'</a> - <a href="'.$track_url.'">'.$track['title'].'</a>
            </div>
            <div id="waveform'.$track['id'].'"></div>
            <div class="waveform-container"></div>
            <div class="small_track__container">
                '.$explicit.'
                <div class="count-holder pc-ministats-custom mx-3">
                    <i class="ion-ios-radio"></i>
                    <span style="font-size: 18px;">'.$count_views.'</span>
                </div>
                <div class="count-holder mx-3">
                    '.$likes_display.' 
                </div>
                <div class="count-holder pc-ministats-custom mx-3">
                    <a href="'.getAudio($track['audio']).'" id="download-link" download="'.$framework->safeLinks($artist_name.' '.$track['title'], 1).'">
                        <i class="ion-ios-cloud-download not-added"></i>
                    </a>
                </div>
                <div class="mx-3">
                    '.clickLike(2, $track['id'], $user['uid']).'
                </div>
                <div class="mx-3">
                    '.showMore_button(1, $track['id'], '').'
                </div>
            </div>
        </div>
    </div>
    <div id="now-waving" style="display: none;">0</div>
    <div id="real-play'.$track['id'].'" style="display: none;">0</div>
    <div class="wave_init" data-track-url="'.getAudio($track['audio']).'" data-track-id="'.$track['id'].'" data-track-format="'.$t_format.'"></div>';
    return $track ? $card : notAvailable('This '.$role.' has no popular tracks', 'no-padding ');
}

function trackDetail__card($trackArr, $type) {
    return mostPopular($trackArr, $type);
}

function getPage($page = null) {
    if ($page == 'artist') {
        $page = 'profile';
    } elseif ($page == 'listen') {
        if ($_GET['to'] == 'albums') {
            $page = 'albums';
        } elseif ($_GET['to'] == 'tracks') {
            $page = 'tracks';
        }
    } elseif ($page == 'playlist') { 
        $page = 'playlist';
    } elseif ($page == 'view_artists') { 
        $page = 'artists';
    } elseif ($page == 'follow') { 
        if ($_GET['get'] == 'followers') {
            $page = 'followers';
        } elseif ($_GET['get'] == 'following') {
            $page = 'following';
        } 
    } elseif ($page == 'project') { 
        $page = 'projects'; 
    } elseif ($page == 'homepage') { 
        $page = 'homepage'; 
    } else {
        $page = $page;
    }
    return $page;
} 

/**
 * Create the links for the navigation navbar nav of the distribution portal
**/
function superNavigation($user_id) {
    global $SETT, $user, $framework, $databaseCL;  
    $new_release = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=new_release');
    $all_releases = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases');
    $artist_services = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=artist-services');
    $reports = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=reports');
    $support = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=support');

    $linkers = array(
        'new_release' => array('New Release',  $new_release), 
        'releases' => array('Discography',  $all_releases),
        'artist-services' => array('Artist Services',  $artist_services),
        'support' => array('Community and Support',  $support),
    );
    
    $rows = '';
    foreach ($linkers as $key => $value) {
        if ($key == $pager) {
            $active = ' class="active"';
        } else {
            $active = '';
        }
        $rows .= '<li'.$active.'><a href="'.$value.'">'.strtoupper($key).'</a></li>';
    }
}

/**
 * Create the links for the navigation navbar nav of the secondary user navigation
**/
function secondaryNavigation($user_id) {
    global $SETT, $user, $framework, $databaseCL; 
    $artist = $framework->userData($user_id, 1);
    $followers = cleanUrls($SETT['url'] . '/index.php?page=follow&get=followers&artist='.$artist['uid']);
    $tracks = cleanUrls($SETT['url'] . '/index.php?page=listen&to=tracks&artist='.$artist['uid']);
    $albums = cleanUrls($SETT['url'] . '/index.php?page=listen&to=albums&artist='.$artist['uid']);
    $artists = cleanUrls($SETT['url'] . '/index.php?page=view_artists&artist='.$artist['uid']);
    $home = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']);
    $playlists = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist=list&creator='.$artist['uid']);
    $projects = cleanUrls($SETT['url'] . '/index.php?page=project&creator='.$artist['uid']);

    $linkers = array('profile' => $home, 'projects' => $projects, 'albums' => $albums, 'tracks' => $tracks, 'playlist' => $playlists, 'artists' => $artists, 'followers' => $followers);
    $pager = getPage($_GET['page']); 
    $rows = '';
    foreach ($linkers as $key => $value) {
        if ($key == $pager) {
            $active = ' class="active"';
        } else {
            $active = '';
        }
        $rows .= '<li'.$active.'><a href="'.$value.'">'.strtoupper($key).'</a></li>';
    }

    $card = '
    <div class="custom-navigation">
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#passNavigation">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button> 
                </div>
                <div class="collapse navbar-collapse" id="passNavigation">
                    <ul class="nav navbar-nav navbar-right">
                        '.$rows.'
                    </ul>
                </div>
            </div>
        </nav>
    </div>';
    return $user ? $card : '';
}

function showViewers($type = null, $id = null) { 
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $fetch_viewers = $databaseCL->fetchViewers($type, $id);
    
    $artists = '';
    if ($fetch_viewers) {
        $c = 0;
        foreach ($fetch_viewers as $key => $veiwers) {
            $c++ ;
            if ($c == 10) {
                break;
            }
            $user_url = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$veiwers['username']);
            $veiwers_name = $veiwers['fname'].' '.$veiwers['lname'].$veiwers['uid'];
            $tt = ' data-toggle="tooltip" title="'.$veiwers_name.'" data-placement="left"';
            $artists .= '
            <a href="'.$user_url.'"'.$tt.'>
              <img src="'.getImage($veiwers['photo'], 1).'" alt="'.$veiwers_name.'" />
            </a>';           
        }      
    } else {
        $artists = '<div style="height: 30px; width: auto;"></div>';
    }
    $card = '
    <div class="artist__navigation__friends">
        '.$artists.'
    </div>'; 
    return $card;
}

function sidebarStatistics($id, $type=null) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $theme = new themer('explore/stats'); $section = '';

    $track_list = $databaseCL->fetchTracks($id, 5);
    $databaseCL->track_list = implode(',', $track_list);
    $fetch_stats = $databaseCL->fetchStats($type, $id)[0];// Type here is null
    
    $PTMPL['last_24'] = $marxTime->numberFormater($fetch_stats['today']);
    $PTMPL['last_week'] = $marxTime->numberFormater($fetch_stats['last_week']);
    $PTMPL['total'] = $marxTime->numberFormater($fetch_stats['total']);

    $statistics = $theme->make();
    return $statistics;
}

function sidebar_userSuggestions($artist_id) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $theme = new themer('explore/suggested_users'); $suggestions = $suggested = '';

    // Fetch suggested users (If there are no recommendations fetch all users)
    $related = [];
    $default = $databaseCL->fetchFollowers($artist_id);
    if ($default) {
        $last_follow = array_reverse($default)[0];
        $databaseCL->fname = $last_follow['fname'];
        $databaseCL->lname = $last_follow['lname'];
        $databaseCL->label = $last_follow['label'];
        $databaseCL->limit = $configuration['sidebar_limit'];
        $related = $databaseCL->fetchRelated($last_follow['uid'], 1);
    } else {
        $framework->limited = $configuration['sidebar_limit'];
        $related = $framework->userData(null, 0);
    }
    if ($related) { 
        shuffle($related);
        $suggested = '';
        foreach ($related as $rows) { 
            $template = new themer('explore/special_users_cards'); $section = '';
            $tracks_count = $databaseCL->fetchTracks($rows['uid'], 3)[0]['counter'];
            $PTMPL['tracks_count'] = $marxTime->numberFormater($tracks_count, 1);
            $PTMPL['tracks_link'] = cleanUrls($SETT['url'].'/index.php?page=listen&to=artist&artist='.$rows['uid']);

            $follower_count = $databaseCL->fetchFollowers($rows['uid'], 1)[0]['counter'];
            $PTMPL['follower_count'] = $marxTime->numberFormater($follower_count, 1);
            $PTMPL['followers_link'] = cleanUrls($SETT['url'].'/index.php?page=follow&get=followers&artist='.$rows['uid']);
            $PTMPL['follow_btn'] = clickFollow($rows['uid'], $user['uid']);

            $PTMPL['prof_link'] = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$rows['username']);
            $PTMPL['prof_name'] = $rows['fname'] . ' ' . $rows['lname'];
            $PTMPL['prof_photo'] = getImage($rows['photo'], 1);
            $PTMPL['verif_badge'] = $rows['verified'] ? ' verifiedUserBadge' : '';
            $PTMPL['sidebar_title'] = 'Who to follow';
            $PTMPL['refresher'] = '<i class="ion-ios-refresh-circle pc-type-h3"></i> Refresh';

            $suggested .= $template->make();
        }     
        $PTMPL['suggested_users'] = $suggested;  
    }

    $databaseCL->limit = null;
    $suggestions = $theme->make();
    return $suggestions;
}    

function sidebar_trackSuggestions($artist_id, $titler = 'Likes') {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $theme = new themer('explore/suggested_tracks'); $suggestions = $suggested = '';
    
    $track_likes = $databaseCL->listLikedItems($artist_id, 2);
    if ($track_likes) {
        $PTMPL['top_title'] = $titler;
        $PTMPL['total_likes'] = $marxTime->numberFormater($track_likes[0]['likes']);
        $PTMPL['all_likes_link'] = cleanUrls($SETT['url'] . '/index.php?page=listen&to=tracks&artist='.$artist_id);
        shuffle($track_likes);

        foreach ($track_likes as $rows) { 

            $suggested .= smallTracks_Card($rows['id']);

        }
    }
    $PTMPL['suggested_track_cards'] = $suggested; 
    $suggestions = $theme->make();
    return $suggestions;
}

function releaseType($id, $type = null) {
    global $databaseCL;
    $r_audio = $databaseCL->fetchRelease_Audio(null, $id);
    $r_audio_count = $r_audio ? count($r_audio) : 0;

    if ($r_audio_count > 0) {
        if ($r_audio_count >= 13) {
           $rt = 4;
           $ic = '<span class="mr-4"><i class="ion-ios-filing"></i> Extended Album</span>';
        } elseif ($r_audio_count > 5 && $r_audio_count < 13) {
           $rt = 3;
           $ic = '<span class="mr-4"><i class="ion-ios-albums"></i> Album</span>';
        } elseif ($r_audio_count > 1 && $r_audio_count < 6) {
           $rt = 2;
           $ic = '<span class="mr-4"><i class="ion-ios-clock"></i> EP</span>';
        } else {
           $rt = 1;
           $ic = '<span class="mr-4"><i class="ion-ios-disc"></i> Single</span>';
        }
        if ($type) {
            return $ic;
        } else {
            return $rt;
        }
    }
    return;
}

/**
 * This functions displays the information for release details
 * @param  variable $id is the unique id of the release (release_id)
 * @return html     returns the HTML of the card details
 */
function releasesCard($id = null) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $theme = new themer('distribution/global/release_card_pending'); $release = '';
    
    $get_release = $databaseCL->fetchReleases(1, $id)[0];
    $r_artist = $databaseCL->fetchRelease_Artists(1, $id)[0];
    $r_audio = $databaseCL->fetchRelease_Audio(null, $id);
    $r_audio_count = $r_audio ? count($r_audio) : 0;

    $PTMPL['approve_button'] = $user['role'] >= 4 && $get_release['status'] == 2 ? '<button class="btn btn-success mx-2">Approve</button>' : '';

    $PTMPL['release_audio_count'] = $r_audio_count > 0  ? '<span class="mr-4"><i class="ion-ios-musical-notes"></i> '.$r_audio_count.' Tracks</span>' : '';
    $PTMPL['release_title'] = $get_release['title'];

    $release_home_url = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&rel_id='.$get_release['release_id']);
    $release_publ_url = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&set=publish&rel_id='.$get_release['release_id']);
    $release_remove_url = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=manage&modify=remove&rel_id='.$get_release['release_id']);

    $PTMPL['release_upc'] = $get_release['upc'];
    $PTMPL['release_artist'] = $r_artist ? '<span class="mr-4"><i class="ion-ios-microphone"></i> '.$r_artist['name'].'</span>' : '';
    $PTMPL['create_date'] = $marxTime->dateFormat($get_release['date'], 2);
    $PTMPL['release_artwork'] = getImage($get_release['art'], 1);

    $PTMPL['release_album_single'] = releaseType($id, 1);

    $step_1 = $get_release['title'] != '' ? $marxTime->percenter(10, 100) : 0;
    $step_2 = $get_release['c_line'] != '' && $get_release['p_line'] != '' && $get_release['label'] != '' && $get_release['release_date'] != '' ? $marxTime->percenter(25, 100) : 0;
    $step_3 = $r_audio_count > 0 ? $marxTime->percenter(25, 100) : 0;
    $step_4 = $get_release['art'] != '' ? $marxTime->percenter(25, 100) : 0;
    $step_5 = $r_artist != '' ? $marxTime->percenter(15, 100) : 0;

    $PTMPL['release_progress'] = $progress = $step_1 + $step_2 + $step_3 + $step_4 + $step_5;

    $missing_artist = !$r_artist && $progress == 60 ? ' '.$LANG['missing_artist'] : '';

    $PTMPL['release_progress_text'] = $progress == 100 ? sprintf($LANG['release_complete'], $progress) : sprintf($LANG['release_almost_complete'].$missing_artist, $progress);
    $PTMPL['release_button'] = $progress !== $progress ? '<a href="'.$release_publ_url.'" class="btn btn-success float-right mx-2">Publish Release</a>' : '<a href="'.$release_home_url.'" class="btn btn-primary float-right mx-2">Complete Release</a>'; 

    $PTMPL['delete_button'] = $get_release['status'] == 1 ? '<a href="" title="Permanently Delete"><i class="fa fa-trash float-right m-3 text-white fa-3"></i></a>' : '';
    $PTMPL['edit_button'] = $get_release['status'] == 3 ? '<a href="'.$release_home_url.'" title="Change meta data"><i class="fa fa-edit float-right m-3 text-white fa-3"></i></a>' : '';
    $PTMPL['remove_button'] = $get_release['status'] == 3 ? '<a href="'.$release_remove_url.'" title="Remove From Sale"><i class="fa fa-times-circle float-right m-3 text-white fa-3"></i></a>' : '';

    $PTMPL['footer_content'] = $get_release['status'] == 1 ?
    '<div class="pc_rel__footer"> 
        <span class="pc_rel__notice-title">
            '.$PTMPL['release_progress_text'].'
            '.$PTMPL['release_button'].'
        </span> 
        <div class="progress mt-auto" style="height:10px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width:'
            .$PTMPL['release_progress'].'%"></div>
        </div>
    </div>'  :  releasesTracklist($id, 1);
  
    $databaseCL->status = null;
    $release = $theme->make();
    return $release;
} 

function releasesTracklist($id = null, $hidden = null) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 

    $get_release = $databaseCL->fetchReleases(1, $id)[0];
    $r_audio = $databaseCL->fetchRelease_Audio(null, $id);
    $r_audio_count = $r_audio ? count($r_audio) : 0;

    $list_tracks = '';$i = 0;
    if ($r_audio) {
        foreach ($r_audio as $t_key => $tracks) {
            $i++;
            $isrc = $tracks['isrc'] ? $tracks['isrc'] : 'N/A';
            $list_tracks .= '
            <div class="pc_rel__track-row d-flex justify-content-between">
                <span role="index">'.$i.'</span> 
                <span role="title">'.$tracks['title'].'</span> 
                <span role="isrc">'.$isrc.'</span> 
                <span role="duration">'.$duration = fileInfo($tracks['audio'])['playtime_string'].'</span>
            </div>';
        } 
    }

    $button = $hide = '';
    if ($hidden) {
        $button = 
        '<div class="pc_rel__tracks-header" id="track'.$get_release['id'].'" onclick="toggleList('.$get_release['id'].')">
            <span>View Tracklist</span>
            <span><i class="fa fa-chevron-down"></i></span>
        </div>';
        $hide = ' d-none';
    }

    $list = 
    '<div class="pc_rel__tracks"> 
        '.$button.'
        <div class="pc_rel__tracks-content'.$hide.'" id="track_list'.$get_release['id'].'">
            <div class="pc_rel__track-row-headers d-flex justify-content-between">
                <span role="index">#</span> 
                <span role="title">Title of Track</span> 
                <span role="isrc">ISRC</span> 
                <span role="duration">
                    <i class="fa fa-clock-o"></i>
                </span>
            </div> 
             '.$list_tracks.'
        </div>
    </div>';
    return $list;
}

function dataSet($type = null, $date = null) {
    global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL, $marxTime, $page_name;
    
    if ($date = null) {
        $date = date('Y-m-d', strtotime('today'));
    }

    $data = [];
    if ($type == 1) {
        $databaseCL->type = 2;
        $databaseCL->limit = 4;
        $fetch = $databaseCL->releaseStats($user['uid'], $date);

        if ($fetch) {
            foreach ($fetch as $q => $track) {
                $data[] = '{ label: "'.$track['title'].'", value: "'.$track['views'].'" }';
            }
        }
    } elseif ($type == 2) {
        $databaseCL->type = 2; 
        $fetch = $databaseCL->releaseStats($user['uid'], $date);

        if ($fetch) {
            foreach ($fetch as $q => $track) {
                $data[] = '{track: "'.$track['title'].'", views: "'.$track['views'].'" }';
            }
        }
    } else {
        $databaseCL->type = 1;
        $fetch = $databaseCL->releaseStats($user['uid'], $date);

        if ($fetch) {
            foreach ($fetch as $q => $quarter) {
                $data[] = '{ quarter: "2019 Q'.$quarter['qt'].'", views: "'.$quarter['quarterly_views'].'" }';
            }
        }
    }
    $data_set = implode(', ', $data);

    $databaseCL->type = $databaseCL->limit = null;
    return '['.$data_set.']';
}

function showMore_button($type = 0, $item_id = null, $x='More') {
    global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL, $marxTime, $page_name;
    $download = $un_playlist = $t_id = '';

    $databaseCL->track = $t_id = $item_id;  
    $track = $type == 1 ? $databaseCL->fetchTracks(null, 2)[0] : $databaseCL->fetchAlbum($item_id)[0];
    $creator = $type == 1 ? $track['artist_id'] : $track['by']; 
    $artist = $type == 1 ? $framework->userData($creator, 1) : $framework->userData($creator, 1); 
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $page = getPage($page_name);

    if ($type == 1) {
        $track_link = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$track['safe_link']);
        $t_format = strtolower(pathinfo($track['audio'], PATHINFO_EXTENSION));
        $download = '<li><a href="'.getAudio($track['audio']).'" id="download-link-'.$track['id'].'" download="'.$framework->safeLinks($artist_name.' '.$track['title'], 1).'.'.$t_format.'"><i class="fa fa-cloud-download mx-2"></i> Download</a></li>'; 
    } elseif ($type == 2) {
        $databaseCL->track = null;
        $albums = $track;
        $track_link = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$albums['safe_link']);
        $album = $databaseCL->albumEntry($item_id);
        if ($album) {
            $rows = [];
            foreach ($album as $value) {
                $rows[] .= $value['id'];
            }
            $t_id = implode(',', $rows);
        }
    }
    if ($page == 'playlist') {
        $playlist = $databaseCL->fetchPlaylist($_GET['playlist'])[0];
        $plst = $playlist['id'];
        $un_playlist = '
        <li><a href="#" onclick="deleteItem({\'type\': \'1\', \'action\': \'pl_entry\', \'track\': \''.$t_id.'\', \'pl\': \''.$plst.'\'})"><i class="ion-ios-remove-circle-outline"></i> Remove from playlist</a></li>';
    }
    $access = allowAccess($creator);
    $delete = $access ? '
    <li><a href="#"><i class="ion-ios-trash mx-2"></i> Delete</a></li>' : '';

    $playlister = ' 
    <li>
      <a href="#"
        class="playlist-modal-show"
        id="playlist-modal-show"
        data-toggle="modal" 
        data-target="#playlistModal"
        onclick="playlist_modal(2, {\'action\': \'a2list\', \'track\': \''.$t_id.'\', \'type\': '.$type.'})">
        <i class="ion-ios-add-circle-outline"></i> '.$LANG['add_to_playlist'].'
      </a>
    </li>';

    $dropdown = '
    <span class="dropdown mx-3" style="text-transform: none; letter-spacing: normal; font-size: unset;">
        <button class="button-light more" type="button" id="moreDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" title="More Options">
            <i class="ion-ios-more"></i> '.$x.'
        </button>
        <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="moreDropdown"> 
            '.$playlister.'
            '.$un_playlist.'
            <li><a href="#"id="copyable" data-clipboard-text="'.$track_link.'">
                    <i class="ion-ios-copy mx-2"></i> Copy Link 
                </a>    
            </li>
            '.$download.'
            '.$delete.'
        </ul>
    </span>';
    return $user ? $dropdown : ''; 
}

function clickLike($type, $item_id, $user_id, $x = 1) {
    global $SETT, $user, $framework, $databaseCL; 
    // $type: 2 = Track Likes
    // $type: 1 = Album Likes

    $likes = $databaseCL->userLikes($user_id, $item_id, $type);
    $liked = $likes ? ' text-danger' : '';
    $e = !$likes ? '-empty' : '';
    $liked_t = $likes ? 'Unlike' : 'Like';
    $hover = $x !== 1 ? ' text-hover-zoom' : '';

    $button = '<button data-icon-only="'.$x.'" data-like-id="'.$item_id.'" data-type="'.$type.'" class="dolike button-white" id="doLike_'.$item_id.'"><i class="ion-ios-heart'.$liked.'"></i> <span class="text">'.$liked_t.'</span></button>';

    $div_button = '<div data-like-id="'.$item_id.'" data-type="'.$type.'" class="dolike" id="doLike_'.$item_id.'"><i class="ion-ios-heart'.$e.$hover.' '.$liked.'"></i></div>'; 

    $button = $x == 1 ? $button : $div_button; 
    return $user ? $button : '';
}

function clickSubscribe($playlist, $user_id) {
    global $SETT, $user, $framework, $databaseCL; 
    // $type: 2 = Track Likes
    // $type: 1 = Album Likes
 
    $databaseCL->user_id = $user['uid'];
    $follows = $databaseCL->playlistSubscribers($playlist, 2);
    if ($follows) { 
        $text = 'Unsubscribe'; 
        $class2 = ' orange hover';
    } else {  
        $text = 'Subscribe';
        $class2 = '';
    } 

    $button = '<button data-subscribe-id="'.$playlist.'" class="dosubscribe button-dark p-2'.$class2.'" id="doSubscribe_'.$playlist.'"><span class="text">'.$text.'</span></button>'; 
 
    return $user ? $button : '';
}

function clickFollow($leader_id, $follower_id, $x = 1) {
    global $SETT, $user, $framework, $databaseCL; 
    // $type: 2 = Track Likes
    // $type: 1 = Album Likes

    $databaseCL->leader_id = $leader_id;
    $databaseCL->follower_id = $follower_id;
    $follows = $databaseCL->fetchFollowers($leader_id, 1);
    if ($follows) {
        $followed = $e = $uclass = '';
        $text = 'Unfollow';
        $uclass = ' orange hover';
    } else {
        $followed = ' low-blood';
        $text = 'Follow';
        $e = '-add';
        $uclass = '';
    }
    $hover = $x !== 1 ? ' text-hover-zoom' : '';

    $button = '<button data-icon-only="'.$x.'" data-follow-id="'.$leader_id.'" class="dofollow button-dark'.$uclass.'" id="doFollow_'.$leader_id.'"><i class="ion-ios-person'.$e.$followed.'"></i> <span class="text">'.$text.'</span></button>';

    $div_button = '<div data-follow-id="'.$leader_id.'" class="dofollow" id="doFollow_'.$leader_id.'"><i class="ion-ios-person'.$e.$hover.' border'.$followed.'"></i></div>'; 

    $button = $x == 1 ? $button : $div_button; 
    $button = $leader_id !== $follower_id ? $button : ''; 
    return $user ? $button : '';
}

function display_likes_follows($type = null, $item_id = null) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 

    $icon = 'people';
    if ($type == 1 || $type == 2 || $type == 3) {
        $count_ = $databaseCL->LikesCount($type, $item_id)[0];
        $counter_ = $marxTime->numberFormater($count_['total']);
        $counter_full = $marxTime->numberFormater($count_['total'], 1);
        $likes_link = cleanUrls($SETT['url'] . '/index.php?page=track&id='.$item_id.'&likes=view');
        $icon = 'heart';
        $tid = 'likes';
    } elseif ($type == 4) {
        $subcribers_ = $databaseCL->playlistSubscribers($item_id)[0]['total'];  
        $counter_ = $marxTime->numberFormater($subcribers_);
        $counter_full = $marxTime->numberFormater($subcribers_, 1);
        $likes_link = '#';// cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist=subscribers&id='.$item_id);
        $tid = 'subscribers';
    } else {
        $count_ = $databaseCL->fetchFollowers($item_id, 1)[0]['counter'];
        $counter_ = $marxTime->numberFormater($count_);
        $counter_full = $marxTime->numberFormater($count_, 1);
        $likes_link = cleanUrls($SETT['url'] . '/index.php?page=follow&get=followers&artist='.$item_id); 
        $tid = 'followers';
    }

    $card = '
    <div class="hidden" id="'.$tid.'-count-'.$item_id.'">'.$counter_full.'</div>
    <span title="'.$counter_full.' likes" class="pc-ministats-item mx-3">
        <a href="'.$likes_link.'" rel="nofollow" class="pc-ministats pc-ministats-custom">
            <span class="pc-visuallyhidden">View all likes</span>
            <i class="ion-ios-'.$icon.'"></i>
            <span aria-hidden="true" class="'.$tid.'-counter-'.$item_id.'">'.$counter_full.'</span>
        </a>
    </span>';
    return $card;
}

function showFollowers($user_id, $type=null) {
    global $SETT, $framework, $configuration, $databaseCL, $marxTime;
    // 2: Sidebar following
    // 1: Sidebar followers
    // 0: Inner followers
    $databaseCL->limit = $configuration['sidebar_limit']; 
    $followership = $databaseCL->fetchFollowers($user_id, $type);  
    $follower_count = $marxTime->numberFormater($databaseCL->fetchFollowers($user_id, $type)[0]['counter'], 1);
    if ($type == 1) {
        $rel = 'Followers';
        $rel_link = cleanUrls($SETT['url'] . '/index.php?page=follow&get=followers&artist='.$user_id);
    } elseif ($type == 2) {
        $rel = 'Following';
        $rel_link = cleanUrls($SETT['url'] . '/index.php?page=follow&get=followings&artist='.$user_id);
    }
    $card = '';
    $c = 0; 
    if ($followership) {
        $card = '
        <article class="sidebarModule g-all-transitions-200-linear likesModule">
            <a class="sidebarHeader g-flex-row-centered-spread pc-link-light  pc-border-light-bottom" rel="nofollow" href="'.$rel_link.'">
                <h3 class="sidebarHeader__title pc-type-light pc-font-tabular g-flex-row-centered ">
                    <i class="ion-ios-people"></i>
                    <span class="sidebarHeader__actualTitle">
                        <span id="'.strtolower($rel).'-count-'.$user_id.'">'.$follower_count.'</span> '.$rel.'
                    </span>
                </h3>
                <span class="sidebarHeader__more pc-type-h3">View all</span>
            </a> 
            <div class="related-artists pb-3">';
            foreach ($followership as $rows) { 
                $c++ ;
                if ($c == 11) {
                    break;
                }
                $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$rows['username']);
                $card .= smallUser_Card($rows['uid'], null, 1);
            }
            $card .= '
            </div>
        </article>';
    } else {
        $card .= notAvailable('No Followers', 'no-padding ');
    } 
    $databaseCL->limit = null;
    return $followership ? $card : '';
}

function showPlaylist($id = null) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $playlists = $databaseCL->fetchPlaylist($id, 1);

    $show_playlists = '';
    if ($playlists) {
        foreach ($playlists as $rows) {
            $link = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist='.$rows['plid']);
            $cards = '
            <a href="'.$link.'" class="navigation__list__item">
                <i class="ion-ios-musical-notes"></i>
                <span>'.$rows['title'].'</span>
            </a>';
            $show_playlists .= $cards;
        }
    }
    return $show_playlists;
}

function smallUser_Card($id, $button = null, $extra = null) {
    global $SETT, $PTMPL, $user, $framework, $databaseCL, $marxTime;

    $template = new themer('explore/special_users_cards'); $section = ''; 

    $artist = $framework->userData($id, 1);

    $PTMPL['follow_btn'] = !$button ? clickFollow($artist['uid'], $user['uid']) : $button;

    $tracks_count = $databaseCL->fetchTracks($artist['uid'], 3)[0]['counter'];
    $PTMPL['tracks_count'] = $marxTime->numberFormater($tracks_count, 1);
    $PTMPL['tracks_link'] = cleanUrls($SETT['url'].'/index.php?page=listen&to=artist&artist='.$artist['uid']);

    $follower_count = $databaseCL->fetchFollowers($artist['uid'], 1)[0]['counter'];
    $PTMPL['follower_count'] = $marxTime->numberFormater($follower_count, 1);
    $PTMPL['followers_link'] = cleanUrls($SETT['url'].'/index.php?page=follow&get=followers&artist='.$artist['uid']);

    $PTMPL['prof_link'] = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$artist['username']);
    $PTMPL['prof_name'] = $artist['fname'] . ' ' . $artist['lname'];
    $PTMPL['prof_photo'] = getImage($artist['photo'], 1);
    $PTMPL['verif_badge'] = $artist['verified'] ? ' verifiedUserBadge' : '';

    if ($extra) {
        $PTMPL['extra_style_input'] = ' userSuggestionList__extra';
    } 

    $section = $template->make(); 

    return $section; 
}

function smallTracks_Card($track_id, $hidden = null) {
    global $SETT, $PTMPL, $user, $framework, $databaseCL, $marxTime;
 
    $template = new themer('explore/suggested_tracks_cards'); $section = '';

    $databaseCL->track = $track_id;
    $_track = $databaseCL->fetchTracks($user['uid'], 2)[0];

    if ($hidden) {
        $PTMPL['hidden_info'] = hiddenInfo($_track);
    }

    $PTMPL['profile_link'] = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$_track['username']);
    $PTMPL['profile_name'] = $_track['fname'] . ' ' . $_track['lname'];
    $PTMPL['track_link'] = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link']);
    $PTMPL['likes_link'] = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&likes=view');
    $PTMPL['track_title'] = $_track['title'];
    $PTMPL['track_art'] = getImage($_track['art'], 1); 
    $PTMPL['track_id'] = $_track['id'];
    $PTMPL['track_audio'] = getAudio($_track['audio']);
    $PTMPL['track_format'] = strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION));

    $likes = $databaseCL->userLikes($user['uid'], $_track['id'], 2); // check if this track is liked
    $PTMPL['like_button'] = clickLike(2, $_track['id'], $user['uid'], null);
    $PTMPL['like_title'] = $likes ? 'Unlike '.$_track['title'] : 'Like '.$_track['title'];
    $count_likes = $databaseCL->LikesCount(3, $_track['id'])[0];
    $count_views = $databaseCL->fetchStats(1, $_track['id'])[0];
    $PTMPL['likes_count'] = $marxTime->numberFormater($count_likes['total']);
    $PTMPL['views_count'] = $marxTime->numberFormater($count_views['total']);
    $PTMPL['likes_count_full'] = $marxTime->numberFormater($count_likes['total'], 1);
    $PTMPL['views_count_full'] = $marxTime->numberFormater($count_views['total'], 1);

    $section = $template->make(); 
    return $section; 
}

function allowAccess($item_id, $type = null, $forced = false) {
    global $user, $databaseCL;
    $allow_access = false;
    
    $project = $databaseCL->fetchProject($item_id)[0];
    if ($type == 1) {
        // Check if this user is colabing or is the project creator
        $user_colabing = $databaseCL->fetch_projectCollaborators($item_id, 2);
        if ($user_colabing || $user['uid'] == $project['creator_id']) {
            $allow_access = true;
        }  
    } elseif ($type == 2) {
        // Check if this user is colabing or is the project creator else check for the extra forced params
        $top = allowAccess($item_id, 1);
        if ($top) {
           $allow_access = true;
        } elseif ($forced) {
            $allow_access = true;
        }
    } else {
        // Check if the user (item_id) is the logged in user
        if ($item_id == $user['uid']) {
           $allow_access = true;
        }
    }
    return $allow_access;
}

// Project Controllers
function clickApprove($project, $user_id, $type = 0) {
    global $SETT, $LANG, $user, $framework, $databaseCL; 
    // $type: 1 = Request Access
    // $type: 0 = Approve or reject
 
    $databaseCL->user_id = $user_id;
    $check_user = $databaseCL->fetch_projectCollaborators($project, 2)[0]; 
    $check_request = $databaseCL->fetch_colabRequests($project, 1)[0]; 
    $tt = '';
    if ($type == 1) {   
        if ($check_user && $user_id == $user['uid']) {
            $tt = ' data-toggle="tooltip" title="'.$LANG['leave_tip'].'" data-placement="top"';
            $text = 'Leave Project';
            $class2 = ' orange hover';
            $type = 2;
        } elseif ($check_request) { 
            $text = 'Cancel Request';
            $class2 = ' orange hover';
        } else {  
            $class2 = '';
            $text = 'Request Entry';
        } 
    } else {
        if ($check_user) { 
            $tt = ' data-toggle="tooltip" title="'.$LANG['remove_tip'].'" data-placement="left"';
            $text = 'Remove';
            $class2 = ' orange hover';
        } else {  
            $tt = ' data-toggle="tooltip" title="'.$LANG['approve_tip'].'" data-placement="left"';
            $text = 'Approve'; 
            $class2 = '';
        } 
    }

    $button = '<button'.$tt.' data-project-id="'.$project.'" data-user-id="'.$user_id.'" data-type="'.$type.'" class="doapprove button-dark p-2'.$class2.'" id="doAction-'.$type.'_'.$user_id.'"><span class="text">'.$text.'</span></button>'; 
 
    return $user ? $button : '';
}

function sidebar_projectCollaborators($project_id) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $theme = new themer('explore/suggested_users'); $section = '';  

    // Fetch collaborators
    $collabers = $databaseCL->fetch_projectCollaborators($project_id); 
    $project = $databaseCL->fetchProject($project_id)[0];
    $PTMPL['sidebar_title'] = 'Collaborating Artists';
    if ($collabers) {  
        $projected = '';
        foreach ($collabers as $rows) { 
            $template = new themer('explore/special_users_cards');

            if ($user['uid'] == $project['creator_id']) {
                $btn = clickApprove($rows['project'], $rows['user']);
            } else {
                $btn = clickFollow($rows['user'], $user['uid']);
            }
                
            $projected .= smallUser_Card($rows['user'], null, 1);   
        }     
        $PTMPL['suggested_users'] = $projected;  
    } else {
        $PTMPL['suggested_users'] = notAvailable('There are no collaborators for this project');
    }

    $section = $theme->make();
    return $section; 
}

function projectAudio($_track, $type = null, $creator_id=null) {
    global $SETT, $LANG, $user, $framework, $databaseCL, $marxTime; 

    $duration = '00:00';
    $sts = $smsg = $cta = $project_id = $data_type = '';
    
    $iconer = '
        <i class="ion-ios-%s pointer pc-font-3 pc-hover-1"
            data-toggle="tooltip" data-placement="left" title="%s%S">
        </i>';

    $allow_access = 0;
    if ($type == 1 || $type == 2) {
        $prj = $databaseCL->fetchProject($_track['project'])[0];
        if ($type == 1) {
            $audio = $_track['file'];
            $creator = $_track['user'];
            $pid = $_track['project'];
            $title = $_track['title'];
            $_track['title'] =  $prj['title'];
            $sts = !$_track['hidden'] ? 'checkmark-circle green' : 'help-circle pc-orange';
            $cta = $prj['creator_id'] == $user['uid'] ? $LANG['click2update'] : '';
            $smsg = !$_track['hidden'] ? $LANG['approved'].'. ' : $LANG['pending_instrumental'];
            $data_type = 5;
        } elseif ($type == 2) {
            $audio = $_track['file'];
            $creator = $_track['user'];
            $pid = $_track['project'];
            $title = $_track['title'];
            $_track['title'] = $databaseCL->fetchTags($_track['tag'])[0]['title'];
            $sts = $_track['status'] ? 'checkmark-circle green' : 'help-circle pc-orange';
            $cta = $prj['creator_id'] == $user['uid'] ? $LANG['click2update'] : '';
            $smsg = $_track['status'] ? $LANG['approved'].'. ' : $LANG['pending_stem'];
            $data_type = 3;
        }
        $status_button = $creator == $user['uid'] || $prj['creator_id'] == $user['uid'] ? '  
            <a href="#" data-toggle="modal" data-target="#uploadModal" data-track-id="'.$_track['id'].'"
                data-project-id="'.$pid.'" data-type="'.$data_type.'" class="manage-modal-show">
                '.sprintf($iconer, $sts, $smsg, $cta).' </a>' : sprintf($iconer, $sts, $smsg, $cta);
    } else {
        $audio = $_track['instrumental'];
        $creator = $_track['creator_id'];
        $pid = $_track['id'];
        $title = $LANG['main_track'];
        $sts = 'checkmark-circle green';
        $smsg = $LANG['default_track']; 
        $status_button = $creator == $user['uid'] ? ' 
            <a href="#" class="upload-modal-show" id="main-instrumental-modal-show"
                data-toggle="modal" data-type="4" data-target="#uploadModal" data-project-id="'.$pid.'">
                '.sprintf($iconer, $sts, $smsg, $cta).' </a>' : sprintf($iconer, $sts, $smsg, $cta);
    }

    $status = '
        <div class="stem__extra stem_extra_'.$_track['id'].'" title="'.$smsg.'">
            '.$status_button.'
        </div>';

    if (getAudio($audio, 1)) {
        $duration = fileInfo($audio)['playtime_string'];
    } 
    $project = $databaseCL->fetchProject($pid)[0];
    $artist = $framework->userData($creator, 1);
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $t_format = strtolower(pathinfo($audio, PATHINFO_EXTENSION)); 

    $allow_access = allowAccess($pid, 2, $project['published']);
    $download_link = $allow_access ? '
        <div class="track__download" title="Download '.$title.'">
            <a href="'.getAudio($audio).'" id="download-link" download="'.strtoupper($title.'-'.$_track['title']).'.'.$t_format.'">
                <i class="ion-ios-cloud-download not-added pc-shadow-1"></i>
            </a>
        </div>' : '';

    $track = $audio ? '
        <div class="tracker song-container stem_'.$_track['id'].'" id="track'.$_track['id'].'"> 
            <div class="track__added subst"> 
                <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="play'.$_track['id'].'" data-track-url="'.getAudio($audio).'" data-track-format="'.$t_format.'"  data-hideable="0" class="track">
                    <div class="tracker__bg_art" style="background-image: url(&quot;'.getImage($project['cover'], 1, 2).'&quot;)">
                        <i class="ion-ios-play" id="icon_play'.$_track['id'].'"></i> 
                        <img style="display: none;" src="'.getImage($project['cover'], 1, 2).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'">
                    </div>
                </div>
            </div>
            <div class="stem__title">
                <span id="song-url'.$_track['id'].'"> ' .$title. ' </span>
            </div>
            <div class="track__author" id="song-author">
                <a href="#" id="song-author'.$_track['id'].'"><div id="song-name'.$_track['id'].'">'.$_track['title'].'</div></a>
            </div>
            '.$download_link.'
            <div class="track__length">'.$duration.'</div> 
            '.$status.'
        </div> ' : ''; 
    return $track;
}

function projectStems($rows) {
    global $SETT, $LANG, $user, $framework, $databaseCL, $marxTime;
    $card = '';
    if ($rows) { 
        $artist = $framework->userData($rows['user'], 1);
        $colaber = $databaseCL->fetch_projectCollaborators($rows['user'], 1)[0];

        $get_stems = $databaseCL->fetchStems($rows['user']);
        $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']);
        $like_button = clickFollow($rows['user'], $user['uid']);

        $n = 0;
        if ($get_stems) {
            $track = $album = '';
            foreach ($get_stems as $_track) {
                $n++;
                $track .= projectAudio($_track, 2);
            }
            $list_tracks = $track;
        } else {
            $list_tracks = notAvailable($LANG['pending_user_stem']);
        }

        $card .= '
        <div class="album">
            <div class="album__info">
                <a href="'.$link.'">
                    <div class="project__stems__art">
                        <img src="'.getImage($artist['photo'], 1).'" alt="'.$artist['fname'].'" />
                    </div>
                </a>
                <div class="album__info__meta">
                    <div class="album__year"> JOINED '.date('d M Y', strtotime($colaber['time'])).'</div>
                    <div class="album__name">
                        <a href="'.$link.'">'.$artist['fname'].' '.$artist['lname'].'</a>
                    </div>
                    <div class="album__actions">
                        '.$like_button.' 
                    </div>
                </div>
            </div>
            
            <div class="album__tracks">
                <div class="tracks">
                    '.$list_tracks.'
                </div>
            </div>
        </div>';
    }
    return $card;
}

function specialRequestCard($rows){
    global $SETT, $user, $framework, $databaseCL, $marxTime; 

    $artist = $framework->userData($rows['user'], 1);
    
    $btn = clickApprove($rows['project'], $rows['user']);
    $projected = smallUser_Card($artist['uid'], null, 1);
    $card ='
    <div class="colab-card" id="special-request-'.$rows['user'].'">
        <div class="colab-card__image" style="background-image: url(&quot;'.getImage($artist['cover'], 1).'&quot;);">
        </div>
        <article class="sidebarModule g-all-transitions-200-linear whoToFollowModule" style="display: block;"> 
            '.$projected.'
        </article> 
    </div>';
    return $card;
}

function notSpecialCard() {
    global $SETT, $user, $framework, $databaseCL, $marxTime; 

    $artist = $framework->userData($rows['user'], 1);
    $card = '
    <a href="'.$link.'" class="related-artist">
        <span class="related-artist__img">
            <img src="'.getImage($artist['photo'], 1, 1).'" alt="'.$artist['fname'].' '.$artist['lname'].'" />
        </span>
        <span class="related-artist__name">'.$artist['fname'].' '.$artist['lname'].'</span>
    </a>';
    return $card;
}

function projectsCard($rows, $artist_id = null) {
    global $SETT, $user, $framework, $databaseCL, $marxTime;
    $artist = $framework->userData($artist_id, 1);

    $creator = $framework->userData($rows['creator_id'], 1);
    $link = cleanUrls($SETT['url'] . '/index.php?page=project&project='.$rows['safe_link']);
    $enter_btn = clickApprove($rows['id'], $user['uid'], 1);  

    $count_stems = $marxTime->numberFormater($rows['count_stems'], 1);
    $count_instrumentals = $marxTime->numberFormater($rows['count_instrumentals'], 1);

    $collabers = $databaseCL->fetch_projectCollaborators($rows['id'])[0];
    $collabers_count = $marxTime->numberFormater($collabers['counter']);
    $collabers_count_full = $marxTime->numberFormater($collabers['counter'], 1); 

    $cards = '
    <div class="col-md-6">
        <div class="artist__card mb-2">
            <div class="free-artist">
                <div class="album-artist__">
                    <a href="'.$link.'">
                        <div class="playlist__image" style="background-image: url(&quot;'.getImage($rows['cover'], 1).'&quot;)">
                            <i class="ion-ios-play"></i>
                        </div>
                    </a>
                </div>
                <a href="'.$link.'">
                    <div class="artist__name">'.$rows['title'].'</div>
                </a>
                <div class="artist__followers">
                    <a href="#" title="'.$collabers_count_full.' Collaborators" class="mr-3"> <i class="ion-ios-people ui ml-3"></i> <span id="collaborators-count-'.$rows['id'].'">'.$collabers_count.'</span></a>
                    <a href="#" title="'.$count_instrumentals.' Instrumentals" class="mr-3"> <i class="ion-ios-musical-notes ui ml-3"></i> '.$count_instrumentals.'</a>
                    <a href="#" title="'.$count_stems.' Stems" class="mr-3"> <i class="ion-ios-pulse ui ml-3"></i> '.$count_stems.'</a>
                </div>
                '.$enter_btn.' By '.$creator['fname'].' '.$creator['lname'].'
            </div>
        </div>
    </div>';
 
    return $cards;
}
