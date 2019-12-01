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

        case 4:
            $alert = null;
            $i = 'times-circle';
            break;

        default:
            $alert = 'info';
            $i = 'exclamation-circle';
            break;
    }
    if ($alert === null) {
        return '<div class="p-2 mx-1">' . $str . '</div>';
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

        case 4:
            $alert = null;
            $i = 'times-circle';
            break;

        default:
            $alert = 'info';
            $i = 'exclamation-circle';
            break;
    }
    if ($alert === null) {
        return '<div class="p-2 mx-1">' . $str . '</div>';
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

function serverErrorNotice($error = 404, $type = 0) {
    global $LANG;
   
    // Format the error to display
    if ($error == 404) {
        $notice = $LANG['error_404'];
    } elseif ($error == 403) {
        $notice = $LANG['error_403'];
    }
    $page_title = $LANG['error']. ' ' .$error;
    if ($type == 0) {
        $theme = new themer('distribution/error');
    }
    return array($theme, $notice, $page_title);
}

/**
 * urlrecoder Recode urls to be safe with .htaccess
 * @param  string  $url  the url to decode or encode
 * @param  integer $type if type is set as 2 or 3 it will 
 * encode and decode urlencode()ed strings
 * @return string        returns the newly created string
 */
function urlrecoder($url, $type = 0) {
    // (:, /, ?, &, =) ()
    if ($type == 2 || $type == 3) {
        $url = str_replace(array('%3A', '%2F', '%3F', '%26', '%3D'), array('__3A', '__2F', '__3F', '__26', '__3D'), $url); 
        if ($type == 3) {
            $url = str_replace(array('__3A', '__2F', '__3F', '__26', '__3D'), array('%3A', '%2F', '%3F', '%26', '%3D'), $url); 
        }
    } else {
        $url = str_replace(array(':', '/', '?', '&', '='), array('__3A', '__2F', '__3F', '__26', '__3D'), $url); 
        if ($type == 1) {
            $url = str_replace(array('__3A', '__2F', '__3F', '__26', '__3D'), array(':', '/', '?', '&', '='), $url); 
        }
    }
 
    return $url;
} 

function seo_plugin($image = null, $title = null, $description = null) {
    global $SETT, $PTMPL, $configuration, $framework, $site_image;

    $twitter = ($configuration['twitter']) ? $configuration['twitter'] : str_ireplace(' ', '', $configuration['site_name']); 
    $facebook = ($configuration['facebook']) ? $configuration['facebook'] : str_ireplace(' ', '', $configuration['site_name']); 
    $title = ($title) ? $title : $configuration['site_name'];   
    $image = ($image) ? getImage($image, 1) : getImage($configuration['intro_banner']);
    $alt = $title.' Banner Image'; 
    $description = strtolower($description ? $description : $configuration['slug']);  
    $desc = $framework->myTruncate($framework->rip_tags($description), 200, ' ', '');
    $keywords = str_ireplace(' ', ', ', $desc);  
    $url = $SETT['url'].$_SERVER['REQUEST_URI'];

    $plugin = '
    <meta name="description" content="' . $desc . '"/>
    <meta name="keywords" content="'.$keywords.'" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="' . $title . '" />
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
    <meta name="twitter:title" content="' . $title . '" />
    <meta name="twitter:site" content="@' . $configuration['site_name'] . '" />
    <meta name="twitter:image" content="' . $image . '" />
    <meta name="twitter:creator" content="@' . $twitter . '" />
    <link rel="canonical" href="' . $url . '" />';
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

function defineLocale($type = null, $set_id = null) { 
    global $user;

    // get locale
    $locale = getLocale($type, $set_id);

    $all_locale = '';
    if ($locale) {
        foreach ($locale as $list) {
            if (isset($user)) {
                if ($type == 1) {
                    if ($user['state'] == $list['name']) {
                        $sel = ' selected = "selected"';
                    } else {
                        $sel = '';
                    }
                } elseif ($type == 2) {
                    if ($user['city'] == $list['name']) {
                        $sel = ' selected = "selected"';
                    } else {
                        $sel = '';
                    }
                } else {
                    if ($user['country'] == $list['name']) {
                        $sel = ' selected = "selected"';
                    } else {
                        $sel = '';
                    }
                }
            } else {
                $sel = '';
            }
            $all_locale .= '<option'.$sel.' value="'.$list['name'].'" id="'.$list['id'].'">'.$list['name'].'</option>';
        }
    } else {
        $stmt = $_POST['type'] == 1 ? 'cities for this state' : 'states for this country';
        $all_locale .= '<option selected="selected" value="">No '.$stmt.'</option>'; 
    }

    return $all_locale;
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
    $default = $SETT['url'] . '/uploads/photos/default.png';
    if (!$image) { 
        $image = $default; 
    }

    $c = null;
    if ($type == 1 || $type == 3) {
      // Uploaded images
      $dir_url = $SETT['url'] . '/uploads/photos/';
      $_dir = $SETT['working_dir'].'/uploads/photos/';
      $c = 1;
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
          $image = $default;
        }
    } elseif ($type == 3)  {
        $image = $dir_url.$image;
        if (@exif_imagetype($image)) {
            $image = $image;
        } else {
            $image = $default;
        }
    } else {
        if (file_exists($_dir.$image) && is_file($_dir.$image)) {
          $image = $dir_url.$image;
        } else {
          $image = $default;
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

function fetchSocialInfo($profile, $type = null, $_style = null) {
    global $configuration, $framework, $user;
        
        // Array: database column name => url model

        $links = ''; $i = $ii = 0;
        if ($type) {
            $social = array( 
                'facebook'      => array('https://facebook.com/%s', 'fb-ic'), 
                'twitter'       => array('https://twitter.com/%s', 'tw-ic'),
                'instagram'     => array('https://instagram.com/%s', 'ins-ic')
            );
            foreach($social as $value => $url) { 
                $ii++;
                $class = $url[1];
                if ($type == 1) {
                    if ($_style == null) {
                        $extra_style = ' class="nav-item"';
                        $extra_a_style = ' class="nav-link waves-effect waves-light"';
                    } else { 
                        if ($ii == 1) {
                           $extra_style = ' class="'.$_style.'"';
                        } else {
                            $extra_style = '';
                        }  
                        $extra_a_style = '';
                    }
                    $links .= ((!empty($profile[$value])) ? '
                    <li'.$extra_style.'>
                        <a'.$extra_a_style.' href="'.sprintf($url[0], $profile[$value]).'" rel="nofllow" title="Follow us on '.ucfirst($value).'">
                            <i class="fab '.icon(3, $value).'"></i>
                        </a>
                    </li>' : ''); 
                } elseif ($type == 2) {
                    $links .= ((!empty($profile[$value])) ? '
                    <a href="'.sprintf($url[0], $profile[$value]).'" rel="nofllow" title="Follow us on '.ucfirst($value).'" class="p-2 m-2 fa-lg '.$class.'"><i class="fa '.icon(3, $value).'"> </i></a>' : '');                        
                } elseif ($type == 3) {
                    $icon = $value !== 'instagram' ? icon(3, $value).'-square' : icon(3, $value);
                    $links .= ((!empty($profile[$value])) ? '
                    <div class="social__link">
                        <a href="'.sprintf($url[0], $profile[$value]).'" rel="nofllow" title="Follow us on '.ucfirst($value).'"><i class="fa '.$icon.'"></i> '.ucfirst($value).'</a>
                    </div>' : '');
                } else {   
                    $links .= ((!empty($profile[$value])) ? '  
                        <a href="'.sprintf($url[0], $profile[$value]).'" rel="nofllow" title="Follow us on '.ucfirst($value).'" class="'.$value.'"><i class="fa '.icon(3, $value).'"></i></a>  ' : '');             
                }
            }
        } else {
            $social = array(
                'facebook'      => array('https://facebook.com/%s', 'fab fa-facebook-f'),
                'twitter'       => array('https://twitter.com/%s', 'fab fa-twitter'),
                'instagram'     => array('https://instagram.com/%s', 'fab fa-instagram'),
                'whatsapp'      => array('whatsapp:%s', 'fab fa-whatsapp'),
                'email'         => array('mailto:%s', 'fas fa-envelope-open'),
                'site_office'   => array('%s', 'fas fa-home')
            );
            foreach($social as $value => $url) {
                $i++;
                $_url = $url[0]; 
                $icon = $url[1]; 
                $item_title = $value == 'site_office' ? 'Address' : $value;
                $url_link = $profile[$value] != $profile['site_office'] ? '<a href="'.sprintf($url[0], $profile[$value]).'" rel="nofllow" title="Follow us on '.ucfirst($value).'" class="text-dark">'.$profile[$value].'</a>' : $profile[$value];
                $offset = (($i%2) == 0) ? ' my-4' : '';
                $links .= '
                <div class="fv3-contact'.$offset.'">
                    <div class="row">
                        <div class="col-2">
                            <span class="'.$icon.'"></span>
                        </div>
                        <div class="col-10">
                            <h6>'.$item_title.'</h6>
                            <p>
                                '.$url_link.'
                            </p>
                        </div>
                    </div>
                </div>';  
            } ;
        }
        return $links;
}

function userAction($type = null) {
    global $SETT, $configuration, $admin, $user, $user_role;

    $dropdown = $user_profile = $sign_out = '';
    if ($admin || $user) {
        $logout_url = cleanUrls($SETT['url'] . '/index.php?page=homepage&logout=admin');
        $logout_user = cleanUrls($SETT['url'] . '/index.php?page=homepage&logout=user');

        $user_link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$user['uid']);
        $user_update_link = cleanUrls($SETT['url'] . '/index.php?page=account&view=update');
        $admin_link = cleanUrls($SETT['url'] . '/index.php?page=admin&view=admin');

        $sign_out .= $admin ? '<a href="'.$logout_url.'" class="dropdown-item">Admin Logout</a>' : '';
        $sign_out .= $user ? '<a href="'.$logout_user.'" class="dropdown-item">Account Logout</a>' : '';        

        $user_profile .= $admin ? '<a class="dropdown-item" href="'.$admin_link.'">Admin Details</a>' : '';
        $user_profile .= $user ? '<a class="dropdown-item" href="'.$user_update_link.'">Update Profile</a>' : '';
        $user_profile .= $user ? '<a class="dropdown-item" href="'.$user_link.'">View Profile</a>' : '';

        if ($type == 1) {    
            $dropdown .= $admin ? '<li><a href="'.$admin_link.'">Admin Details</a></li>' : '';
            $dropdown .= $user ? '<li><a href="'.$user_update_link.'">Update Profile</a></li>' : '';
            $dropdown .= $user ? '<li><a href="'.$user_link.'">View Profile</a></li>' : '';
            $dropdown .= $admin ? '<li><a href="'.$logout_url.'">Admin Logout</a></li>' : '';
            $dropdown .= $user ? '<li><a href="'.$logout_user.'">Account Logout</a></li>' : '';  
        } else {
            $dropdown = '
            <li class="nav-item dropdown">
                <a class="nav-link waves-effect waves-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="" rel="nofllow" title="Account Options">
                    <i class="fa fa-user text-light"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    '.$user_profile.'
                    <div class="dropdown-divider"></div> 
                    '.$sign_out.'
                </div>
            </li>';
        }
    } else {
        $dropdown = $configuration['allow_login'] ? '
        <li class="nav-item dropdown">
            <a class="nav-link waves-effect waves-light" href="'.cleanUrls($SETT['url'].'/index.php?page=account&view=access&login=user&referrer='.urlrecoder($SETT['url'].$_SERVER['REQUEST_URI'])).'" rel="nofllow" title="Login">
                <i class="fa fa-user-o text-light"></i>
            </a>
        </li>' : '';
    }
    return $dropdown;  
}

/**
 * /* This function will convert your urls into cleaner urls
 **/
function cleanUrls($url) {
    global $configuration; //$configuration['cleanurl'] = 1;
    if ($configuration['cleanurl']) {
        $pager['homepage']      = 'index.php?page=homepage';
        $pager['explore']       = 'index.php?page=explore';
        $pager['playlist']      = 'index.php?page=playlist';
        $pager['listen']        = 'index.php?page=listen';
        $pager['view_artists']  = 'index.php?page=view_artists';
        $pager['track']         = 'index.php?page=track';
        $pager['artist']        = 'index.php?page=artist';
        $pager['follow']        = 'index.php?page=follow';
        $pager['project']       = 'index.php?page=project';
        $pager['album']         = 'index.php?page=album';
        $pager['account']       = 'index.php?page=account';
        $pager['static']        = 'index.php?page=static';
        $pager['admin']         = 'index.php?page=admin';
        $pager['distribution']  = 'index.php?page=distribution';
        $pager['search']        = 'index.php?page=search';

        if (strpos($url, $pager['homepage'])) {
            $url = str_replace(array($pager['homepage'], '&user=', '&logout=', '&referrer='), array('homepage', '/', '/logout/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['explore'])) {
            $url = str_replace(array($pager['explore'], '&sets=', '&go=', '&referrer='), array('explore', '/sets/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['playlist'])) {
            $url = str_replace(array($pager['playlist'], '&playlist=', '&id', '&creator=', '&referrer='), array('playlist', '/', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['listen'])) {
            $url = str_replace(array($pager['listen'], '&artist=', '&to=', '&id', '&referrer='), array('listen', '/artist/', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['view_artists'])) {
            $url = str_replace(array($pager['view_artists'], '&artist=', '&id', '&referrer='), array('view_artists', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['track'])) {
            $url = str_replace(array($pager['track'], '&likes=', '&track=', '&id=', '&referrer='), array('track', '/likes/', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['artist'])) {
            $url = str_replace(array($pager['artist'], '&artist=', '&id=', '&referrer='), array('artist', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['follow'])) {
            $url = str_replace(array($pager['follow'], '&artist=', '&follow=', '&get=', '&referrer='), array('follow', '/artist/', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['project'])) {
            $url = str_replace(array($pager['project'], '&creator=', '&project=', '&get=', '&referrer='), array('project', '/creator/', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['album'])) {
            $url = str_replace(array($pager['album'], '&creator=', '&album=', '&get=', '&referrer='), array('album', '/creator/', '/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['account'])) {
            $url = str_replace(array($pager['account'], '&view=access', '&view=', '&login=', '&pagination=', '&cid=', '&r_id=', '&thread=', '&referrer='), array('account', '/login', '/', '/', '/page/', '/', '/receiver/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['static'])) {
            $url = str_replace(array($pager['static'], '&view=', '&pagination=', '&referrer=', '/referrer/'), array('static', '/', '/page/'), $url);
        } elseif (strpos($url, $pager['admin'])) {
            $url = str_replace(array($pager['admin'], '&view=', '&pagination=', '&referrer='), array('admin', '/', '/page/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['distribution'])) {
            $url = str_replace(array($pager['distribution'], '&action=', '&rel=', '&rel_id=', '&artist=', '&stat=', '&modify=', '&set=', '&pagination=', '&pay=', '&referrer='), array('distribution', '/', '/', '/', '/', '/stat/', '/', '/set/', '/page/', '/', '/referrer/'), $url);
        } elseif (strpos($url, $pager['search'])) {
            $url = str_replace(array($pager['search'], '&q=', '&rel=', '&pagination=', '&referrer='), array('search', '/', '/rel/', '/page/', '/referrer/'), $url);
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
        $path = 'photos/';
    } elseif ($type == 2) {
        $path = 'files/';
    } elseif ($type == 3) {
        $dir = $SETT['working_dir'].'/'.$SETT['template_url'].'/img/'; 
    } else {
        $path = 'audio/';
    } 

    if ($type == 3) {
        $file = $dir.$name;
        $fallback = $file;
    } else {
        $fallback = $SETT['working_dir'] . '/uploads/' . $path . $name; 

        if ($framework->trueAjax() || $fb) {
            $file =  '../uploads/' . $path . $name;
        } else {
            $file =  getcwd() . '/uploads/' . $path . $name;
        }  
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
    if (strlen($type) >= 3) {
        $title = '- '.$type.' -';
        $pad = 'text-danger';
        if ($type == 403) {
            $string = 'You do not have sufficient privileges to access the resource you requested!';
        } elseif ($type == 404) {
            $string = 'The resource you requested was not found on this server!';
        }
        $type = 2;
    } else {
        $title = 'No content to see here';
    }
    if ($type == 1) {
        $return = 
        '<div class="p-5 container-fluid text-center shadow-sm border border-info '.$pad.'">
            <div class="'.$pad.'pad-section">  
                <i class="'.$pad.' fa fa-question-circle"></i>
                <p class="small">' . $string . '</p> 
            </div>
        </div>';
    } elseif ($type == 2) {
        $return = 
        '<div class="container-fluid">
            <div class="row mb-4">
                <div class="my-5">
                    <div class="card">
                        <div class="card-body p-5">
                            <h1 class="card-title d-flex justify-content-center">
                            <strong class="text-default">'.$title.'</strong>
                            </h1>
                            <hr>
                            <h1 class="text-center"><i class="'.$pad.' fa fa-question-circle"></i></h1>
                            <div class="row my-3 mx-3 d-flex justify-content-center">
                                <h2 class="'.$pad.'">' . $string . '</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    } else {
        if ($pad == '') {
            $pad = 'display-1';
        }
        $return = 
        '<div class="text-center container-fluid">
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

function userNavContent() {
    global $LANG, $SETT, $PTMPL, $configuration, $framework, $admin, $user, $user_role;

    $set_link = '';
    $theme = new themer('homepage/user_menu'); $section = '';
    $user_dropdown = '   
    <div class="user">
        <div class="user__actions">
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <i class="ion-ios-arrow-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                    %s
                </ul>
            </div>
        </div>
    </div>';
    $about = '<li><a href="'.cleanUrls($SETT['url'] . '/index.php?page=static&view=about').'">About Us</a></li>'; 
    if ($user || $admin) {
        if ($user) {
            $PTMPL['user_photo'] = getImage($user['photo'], 1);
            $PTMPL['user_name'] = ucfirst($user['username']);
            $PTMPL['all_notifications_link'] = cleanUrls($SETT['url'] . '/index.php?page=account&view=notifications'); 

            $set_link .= '<li><a href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$user['username']).'">Profile</a></li>'; 
            $set_link .= '<li><a href="'.cleanUrls($SETT['url'] . '/index.php?page=account').'">My Account</a></li>'; 
            $set_link .= '<li><a href="'.cleanUrls($SETT['url'] . '/index.php?page=homepage&logout=user').'">Log Out</a></li>'; 
        }
        if ($admin) {
            $theme = new themer('homepage/user_menu'); $section = '';
            $admin_user = $framework->userData($admin['admin_user'], 1);

            $PTMPL['user_photo'] = getImage($admin_user['photo'], 1);
            $PTMPL['user_name'] = ucfirst($admin_user['username']);

            $set_link .= '<li><a href="'.cleanUrls($SETT['url'] . '/index.php?page=admin').'">Admin</a></li>'; 
            $set_link .= '<li><a href="'.cleanUrls($SETT['url'] . '/index.php?page=homepage&logout=admin').'">Admin Log Out</a></li>'; 
        }
        $set_link .= $about; 

        $PTMPL['user_links'] = $set_link;
        $section = $theme->make();
    } else {
        $set_link .= $about; 
        $set_link .= '<li><a href="'.cleanUrls($SETT['url'] . '/index.php?page=account&view=access&login=user').'">Login</a></li>'; 
        $section = sprintf($user_dropdown, $set_link);
    }
    return $section;
}

function globalTemplate($type) {
    global $LANG, $SETT, $PTMPL, $contact_, $configuration, $framework, $user, $user_role;
    $messaging = new social;

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
    $PTMPL['explore_url'] = cleanUrls($SETT['url'] . '/index.php?page=explore');
    $PTMPL['listen_tracks_url'] = cleanUrls($SETT['url'] . '/index.php?page=listen&to=tracks');
    $PTMPL['listen_albums_url'] = cleanUrls($SETT['url'] . '/index.php?page=listen&to=albums');
    $PTMPL['view_artists_url'] = cleanUrls($SETT['url'] . '/index.php?page=view_artists');
    $PTMPL['account_url'] = $account_url = cleanUrls($SETT['url'] . '/index.php?page=account'); 
    $PTMPL['site_title_'] = ucfirst($configuration['site_name']);

    $PTMPL['user_session'] = userNavContent();
    if ($user_role >=3) {
      $management = cleanUrls($SETT['url'] . '/index.php?page=management');
      $PTMPL['management'] = simpleButtons("bordered background_green2", 'Manage Site <i class="fa fa-cog"></i>', $management);
    }
    $PTMPL['site_friends'] = $messaging->friendship($user['uid']);
    $PTMPL['find_site_friends'] = cleanUrls($SETT['url'] . '/index.php?page=search&rel=find');

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

function admin_sidebar() {
    global $SETT, $PTMPL, $user, $framework, $databaseCL; 
    $template = new themer('admin/side_bar'); $section = '';
 
    $PTMPL['static_content_link'] = cleanUrls($SETT['url'].'/index.php?page=admin&view=static'); 
    $PTMPL['admin_link'] = cleanUrls($SETT['url'].'/index.php?page=admin&view=admin');
    $PTMPL['cofiguration_link'] = cleanUrls($SETT['url'].'/index.php?page=admin&view=config'); 
    $PTMPL['filemanager_link'] = cleanUrls($SETT['url'].'/index.php?page=admin&view=filemanager'); 
    $PTMPL['admin_url'] = cleanUrls($SETT['url'] . '/index.php?page=admin'); 

    $sidebar_links = array(
        'start'                 => 'Start',
        'static'                => 'Static Content',
        'releases'              => 'Manage Releases',
        'admin'                 => 'Admin Details',
        'config'                => 'Site Configuration',
        'manage_users'          => 'Manage Users',
        'manage_tracks'         => 'Manage Tracks',
        'manage_projects'       => 'Manage Projects',
        'manage_playlists'      => 'Manage Playlists',
        'filemanager'           => 'File Manager'
    );

    $bar_links = '';
    foreach ($sidebar_links as $links => $title) { 
        $active = isset($_GET['view']) && $_GET['view'] == $links ? ' active' : ''; 

        $bar_links .= '<a href="'.cleanUrls($SETT['url'].'/index.php?page=admin&view='.$links).'" class="list-group-item list-group-item-action'.$active.'">'.$title.'</a>';
    }
    $PTMPL['sidebar_links'] = $bar_links; 

    $section = $template->make(); 
    return $section; 
} 

function distroNavigation() {
    global $LANG, $SETT, $PTMPL, $user, $admin, $user_role, $framework;

    $login_url = cleanUrls($SETT['url'].'/index.php?page=account&view=access&login=user&referrer='.urlrecoder($SETT['url'].$_SERVER['REQUEST_URI']));
    $acc_link = '<li><a href="'.$login_url.'">'.$LANG['account'].'</a></li>';

    $linkers = array(
        'distribution'  => array('releases' => 'Discography', 'new_release' => 'New Release'),
        'artist-services' => 'Artist Services',
        'sales-report' => 'Reports and Statistics',
        'account' => array('My Account')
    );   

    $nav_link = '';
    foreach ($linkers as $link => $title) { 
        if (is_array($title)) {
            $sub = '';
            foreach ($title as $sub_link => $sub_title) {
                if ($sub_link == 'account') {
                    $sub = userAction(1);
                } else {
                    $sub .= '<li><a href="'.$sub_link.'">'.$sub_title.'</a></li>';
                }
            }
            if ($link == 'account') {
                $link = $title[0];
            }
            $nav_link .= '
            <li class="drop-down"><a href="">'.ucfirst($link).'</a> 
                <ul>
                    '.$sub.'
                </ul>
            </li>';
        } else {
            $nav_link .= '<li><a href="'.$link.'">'.$title.'</a></li>';
        }
    }

    if ($admin || $user_role >= 3) {
        return $nav_link;
    } else {
        return $acc_link; 
    }
}
 
function superGlobalTemplate($type = null) {
    global $LANG, $SETT, $PTMPL, $contact_, $configuration, $framework, $databaseCL, $user, $admin, $user_role;

    $PTMPL['home_url'] = cleanUrls($SETT['url'] . '/index.php?page=homepage');
    $PTMPL['artists_services'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=artist-services');
    $PTMPL['about_page_url'] = cleanUrls($SETT['url'] . '/index.php?page=static&view=about');
    $PTMPL['contact_page_url'] = cleanUrls($SETT['url'] . '/index.php?page=static&view=contact'); 

    $PTMPL['new_release'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=new_release');
    $PTMPL['all_releases'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=releases');
    $PTMPL['artist_services'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=artist-services'); 
    $PTMPL['sales_report'] = cleanUrls($SETT['url'] . '/index.php?page=distribution&action=sales-report'); 

    $PTMPL['social_linkers'] = fetchSocialInfo($configuration, 3);  
    $PTMPL['social_links'] = fetchSocialInfo($configuration, 1);  
    $PTMPL['fetch_home_nav_social'] = fetchSocialInfo($configuration, 1, 'ml-lg-5');
    $PTMPL['social_links'] .= userAction();       
    $PTMPL['distro_user_links'] = userAction(1);       
    $PTMPL['site_name'] = $configuration['site_name'];  

    $PTMPL['distribution_navigation'] = distroNavigation();

    if ($admin || $user_role >= 4) {
        $moderate = cleanUrls($SETT['url'] . '/index.php?page=admin');
        $PTMPL['admin_url'] = '<a href="'.$moderate.'" class="ml-3"><i class="fa fa-cog"></i> Site Admin </a>';   
    }
 
    // Set footer navigation links
    $nav_list = $foot_list = $foot_list_var = '';
    $databaseCL->limit = 10;
    $databaseCL->start = 0;
    $databaseCL->parent = 'static'; 
    $databaseCL->priority = null;
    $navis = $databaseCL->fetchStatic( null, 1 );

    $foot_list .= '<li><a href="'.$PTMPL['contact_page_url'].'">About Us</a></li>';
    $foot_list_var .= '<li><a href="'.$PTMPL['contact_page_url'].'">Contact Us</a></li>';
    $nav_list .= $configuration['blog_url'] ? '<a class="dropdown-item font-weight-bold" href="'.$configuration['blog_url'].'">Community</a>' : ''; 

    if ($navis) {
        $i = 1;
        foreach ($navis as $link) {
            $i++;
            $view_link = cleanUrls($SETT['url'].'/index.php?page=static&view='.$link['safelink']);
            if ($link['header'] == '1') {
                $hs = 1;
                if ($type == 2) {
                   $nav_list .= '<li><a href="'.$view_link.'">'.$link['title'].'</a></li>';
                } else {
                    $nav_list .= '<a class="dropdown-item waves-effect waves-light font-weight-bold" href="'.$view_link.'">'.$link['title'].'</a>';
                }
            } elseif ($link['footer'] == '1') {
                if ($i > 6) {
                    $foot_list_var .= '<li><a href="'.$view_link.'">'.$link['title'].'</a></li>';
                } else {
                    $foot_list .= '<li><a href="'.$view_link.'">'.$link['title'].'</a></li>';
                }
            }
        }

        $PTMPL['content_menu_link'] = isset($hs) ? '
        <li class="nav-item dropdown ml-4 mb-0">
            <a class="nav-link dropdown-toggle waves-effect waves-light font-weight-bold"
            id="contentMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> COMMUNITY AND SUPPORT </a>
            <div class="dropdown-menu dropdown-primary dropdown-menu-right" aria-labelledby="contentMenuLink">
                 '.$nav_list.' 
            </div>
        </li>' : '';  

        $account_link = cleanUrls($SETT['url'].'/index.php?page=account');
        $jalt = $user ? 'Account' : 'Login/Register';
        $PTMPL['just_account_link'] = ' 
        <li class="nav-item mr-3 font-weight-bold">
            <a class="nav-link" href="'.$account_link.'">'.$jalt.'</a>
        </li> ';

        $PTMPL['distro_support_drop'] = isset($hs) ? $nav_list : '';
    } 

    $PTMPL['footer_list_var'] = $foot_list_var; 
    $PTMPL['footer_list'] = $foot_list;  
    
    $databaseCL->parent = 'footer'; 
    $databaseCL->priority = '3';
    $footro =  $databaseCL->fetchStatic(null, 1)[0];
    if ($footro) {
        $PTMPL['footer_text_title'] = $footro['title'];
        $PTMPL['footer_text'] = $framework->rip_tags($footro['content']);
    } else {
        $PTMPL['footer_text_title'] = $configuration['site_name'];
        $PTMPL['footer_text'] = $framework->rip_tags($configuration['slug']);        
    }

    $databaseCL->reverse = $databaseCL->limit = $databaseCL->start = $databaseCL->parent = $databaseCL->priority = null;

    if ($type == 4) {
        $theme = new themer('distribution/global/home-header'); $section = '';
    } elseif ($type == 3) {
        $theme = new themer('distribution/global/footer'); $section = '';
    } elseif ($type == 2) {
        $theme = new themer('distribution/global/header'); $section = '';
    } elseif ($type == 1) {
        $theme = new themer('distribution/global/mdb-header'); $section = '';
    } else { 
        $theme = new themer('distribution/global/mdb-footer'); $section = '';
    }
    $section = $theme->make();
    return $section;
}  

function showMessageLink($receiver, $i_size = '', $thread = null) {
    global $SETT, $PTMPL, $LANG, $user; 
    $messages = new social;

    if ($thread) {
        $thread = '&thread='.$thread;
    } else {
        $get_messages = $messages->fetchMessages(5, $receiver)[0];
        if ($get_messages) {
            $thread = '&cid='.$get_messages['cid'].'&thread='.$get_messages['thread'];
        }
    }

    // Show the send message link
    $chat_link = cleanUrls($SETT['url'].'/index.php?page=account&view=messages&r_id='.$receiver.$thread);
    $chat_link = ($user && $user['uid'] !== $receiver ? 
        '<a href="'.$chat_link.'" class="text-success" data-toggle="tooltip" title="'.$LANG['send_message'].'" data-placement="auto">
            <i class="fa fa-envelope mx-1 '.$i_size.'"></i>
        </a>' : ''); 
    return $chat_link;
}

function bigBanner($image = '', $type = null, $title = '', $button_link = 'null') {
    global $SETT, $PTMPL, $configuration, $user, $framework, $collage, $marxTime; 
    $template = new themer('distribution/global/banner'); $section = ''; 

    if (!$title) {
        $title = $configuration['site_name'];
    }
    
    // Put a line break on the title
    $retitle =$marxTime->retitle($title);

    $banner_title = explode(' ', $retitle);
    if (count($banner_title) > 2) {
        $ban_title = $banner_title[3];
        $banner_title = str_replace($ban_title, '<span>'.$ban_title.'</span>', $retitle);
    } else {
        $banner_title = $retitle;
    }
    $PTMPL['banner_title'] = $banner_title;

    // Generate button from provided link
    $PTMPL['buttons'] = $framework->generateButton($button_link);
 
    // 
    // <a href="#services" class="btn-services scrollto">Our Services</a>

    if ($type == 2) {
        $PTMPL['banner_image'] = getImage($image);
    } else {
        $PTMPL['banner_image'] = getImage($image, 1);
    }

    $section = $template->make();
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
    // Type 4: Update playlist form
    // Type 3: Add to playlist form
    if ($type == 1) {
        $modal = modal('playlist', '<div class="modal-container"></div>', '<span id="modal-title">Playlist</span>', 2);
        $content = $modal;
    } elseif ($type == 2 || $type == 4) {
        $processor = $SETT['url'].'/connection/uploader.php?action=playlists';
        $action = ($type == 2 ? 'create' : 'edit');
        $action_label = ($type == 2 ? 'Create Playlist' : 'Edit Playlist');
        $set_id = ($type == 4 ? ', \'id\': \''.$track.'\'' : '');
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
                onclick="playlistAction(2, {\'action\': \''.$action.'\''.$set_id.'})">
                <i class="ion-ios-list"></i> '.$action_label.'
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
    $artist_name = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
    $t_format = strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION));

    $count_views = $databaseCL->fetchStats(1, $_track['id'])[0]; 
    $count_views = $marxTime->numberFormater($count_views['total']);

    $track = '
    <div class="tracker song-container" id="track'.$_track['id'].'">
        <div class="track__number">'.$n.'</div>
        <div class="track__added subst"> 
            <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="play'.$_track['id'].'" data-track-url="'.getAudio($_track['audio']).'" data-track-format="'.$t_format.'"  data-hideable="0" class="track">
                <div class="tracker__bg_art" style="background-image: url(&quot;'.getImage($_track['art'], 1).'&quot;)">
                    <i class="ion-ios-play" id="icon_play'.$_track['id'].'"></i> 
                    <img style="display: none;" src="'.getImage($_track['art'], 1).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'">
                </div>
            </div>
        </div>
        <div class="track__title'.$small.'">
            <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&id='.$_track['id']).'" id="song-url'.$_track['id'].'"> <div id="song-name'.$_track['id'].'">'.ucfirst($_track['title']).'</div></a>
        </div>
        <div class="track__author'.$small.'" id="song-author">
            <a href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']).' " id="song-author'.$_track['id'].'"> '.ucfirst($artist_name).' </a>
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
    $artist_name = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
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
    $artist_name = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
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
    $artist_name = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
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
                        <img src="'.getImage($rows['art'], 1, 2).'" alt="'.$rows['title'].' Art" />
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
                            <img src="'.getImage($rows['art'], 1).'" alt="'.$rows['title'].' Art" />
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
        $databaseCL->limit = 100;
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
                $title = $framework->realName($value['username'], $value['fname'], $value['lname']);
                $title = '<a href="'.cleanUrls($SETT['url'] . '/index.php?page=listen&to=artist&artist='.$value['uid']).'">'.$_title.$title.'</a>';
                $databaseCL->genre = $value['uid'];
            } elseif ($type == 4) {
                $v_user = $framework->userData($value['by'], 1);
                $title = $value['title'].' By '.$framework->realName($v_user['username'], $v_user['fname'], $v_user['lname']);
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
        $_track = $databaseCL->fetchTracks(null, 2)[0];

        $databaseCL->title = $_track['title'];
        $databaseCL->artist_id = $_track['artist_id'];
        $databaseCL->label = $_track['label'];
        $databaseCL->pline = $_track['pline'];
        $databaseCL->cline = $_track['cline'];
        $databaseCL->genre = $_track['genre'];
        $databaseCL->tags = $_track['tags'];     
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
                        .' - <span class="feature-artist">'.$framework->realName($artist['username'], $artist['fname'], $artist['lname']).'</span>
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
                        .' By <span class="feature-artist">'.$framework->realName($artist['username'], $artist['fname'], $artist['lname']).'</span>
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
    $fullname = $framework->realName($rows['username'], $rows['fname'], $rows['lname']);
    $carder = '
        <div class="media-card">
            <a href="%s">
                <div class="media-card__image" style="background-image: url(&quot;%s&quot;);">
                    <i class="ion-ios-open"></i>
                </div>
            </a>
            <a  href="%s" class="media-card__footer">%s</a>
        </div>';
    return sprintf($carder, $link, getImage($rows['photo'], 1), $link, $fullname);
}

function printSearch($rows) {
    global $SETT, $user, $configuration, $databaseCL, $framework;
    // $type = 1: Show users
    // $type = 2: Show Tracks
    // $type = 3: Show Albums
    // $type = 4: Show Albums
    // $type = 5: Show Instrumental

    $description = ' data-toggle="tooltip" title="'.$framework->myTruncate($rows['description'], 150).'" data-placement="auto"';
    $type = $rows['type'];
    $title = $rows['title'];
    $chat_link = '';
    if ($type == 1) { 
        $follow_btn = clickFollow($rows['id'], $user['uid']);
        $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$rows['safe_link']);    
        $title = $framework->realName($rows['safe_link'], $rows['title']).$follow_btn;
    } elseif ($type == 2) {
        $link = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$rows['safe_link'].'&id='.$rows['id']);    
    } elseif ($type == 3) {
        $link = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$rows['safe_link']);    
    } elseif ($type == 4) {
        $link = cleanUrls($SETT['url'] . '/index.php?page=project&project='.$rows['safe_link']);    
    } elseif ($type == 5) {
        $link = '#download_'.$rows['safe_link'];    
    } else {
        $databaseCL->user_id = $user['uid']; 
        $featured = $databaseCL->playlistEntry($rows['id'], 1)[0]; 
        $rows['art'] = $featured['art'];
        $link = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist='.$rows['safe_link']);  
    }
    $carder = '
        <div class="media-card" '.$description.'>
            <a href="%s">
                <div class="media-card__image" style="background-image: url(&quot;%s&quot;);">
                    <i class="ion-ios-open"></i>
                </div>
            </a>
            <a  href="%s" class="media-card__footer">%s</a>
        </div>';
    return sprintf($carder, $link, getImage($rows['art'], 1), $link, $title);
}

function showTags($str) {
    global $SETT;

    $string = explode(',',$str);
    $tags = ''; 
    if ($str) {
        foreach ($string as $list) {
            $link = cleanUrls($SETT['url'] . '/index.php?page=search&q='.urlencode('#'.$list).'&rel=search'); 
            $tags .= '
              <a href="'.$link.'"><span class="badge badge-secondary"># '.$list.'</span></a>';
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
                <img src="'.getImage($artist['photo'], 1, 1).'" alt="'.$framework->realName($artist['username'], $artist['fname'], $artist['lname']).'" />
            </div>
            <a href="'.$link.'">
                <div class="artist__name">'.$framework->realName($artist['username'], $artist['fname'], $artist['lname']).'</div>
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
    global $SETT, $LANG, $user, $framework, $databaseCL, $marxTime;
    $artist = $framework->userData($artist_id, 1);

    $playlists = $databaseCL->fetchPlaylist($artist_id, 1);
    $ud = $framework->userData($artist_id, 1);
    $creator_fullname = $framework->realName($ud['username'], $ud['fname'], $ud['lname']);

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
            
            $creator_fullname = $framework->realName($rows['username'], $rows['fname'], $rows['lname']);
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
                    '.$subscribe_btn.' Created by '.$creator_fullname.'
                </div>
            </div>';
            $show_playlists .= $cards;
        }
    } else {
        $show_playlists .= notAvailable($creator_fullname. ' has not created any ' .$LANG['playlists']);
    }
    return $show_playlists;
}

function mostPopular($artist_id, $type=null) {
    global $SETT, $user, $framework, $databaseCL, $marxTime;

    if ($type) {
        $track = $artist_id;
        $userData = $framework->userData($track['artist_id'], 1); 
        $artist_name = $framework->realName($userData['username'], $userData['fname'], $userData['lname']);
        $_artist_id = $track['artist_id'];
        $username = $userData['username'];
    } else {
        $track = $databaseCL->fetchTracks($artist_id, 1)[0];
        $artist_name = $framework->realName($track['username'], $track['fname'], $track['lname']);
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
            <img class="hidden__item" src="'.getImage($track['art'], 1, 2).'" id="song-art'.$track['id'].'" alt="'.$track['title'].' Art">
            <a class="hidden__item"href="'.$track_url.'" id="song-url'.$track['id'].'"> <div id="song-name'.$track['id'].'">'.$track['title'].'</div></a>  
            <a class="hidden__item" href="'.$user_url.' " id="song-author'.$track['id'].'"> '.$artist_name.' </a>
        </div>

        <div class="latest-release__song">
            <div class="latest-release__song__title__ pc-font-1_6">
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
    $user = null;
    return $track ? $card : notAvailable('This '.$role.' has no popular tracks', 'no-padding ');
}

function trackDetail__card($trackArr, $type) {
    return mostPopular($trackArr, $type);
}

function getPage($page = null) {
    if ($page == 'artist') {
        $page = 'profile';
    } elseif ($page == 'listen') {
        if (isset($_GET['to'])) {
            $page = $_GET['to'];
        } 
    } elseif ($page == 'playlist') { 
        $page = 'playlist';
    } elseif ($page == 'view_artists') { 
        $page = 'artists';
    } elseif ($page == 'follow') { 
        if (isset($_GET['get'])) {
            $page = $_GET['get'];
        } 
    } elseif ($page == 'account') { 
        if (isset($_GET['view'])) {
            $page = $_GET['view'];
        }
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
    return $user_id ? $card : '';
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
            $veiwers_name = $framework->realName($veiwers['username'], $veiwers['fname'], $veiwers['lname']).$veiwers['uid'];
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
    
    $PTMPL['sidebar_title'] = 'Who to follow';
    $PTMPL['who_to_follow_link'] = cleanUrls($SETT['url'].'/index.php?page=search&rel=find');
    $PTMPL['refresher'] = '<i class="ion-ios-refresh-circle pc-type-h3"></i> Refresh';
    if ($related) { 
        shuffle($related);
        $suggested = '';
        foreach ($related as $rows) { 
            // $template = new themer('explore/special_users_cards'); $section = '';
            // $tracks_count = $databaseCL->fetchTracks($rows['uid'], 3)[0]['counter'];
            // $PTMPL['tracks_count'] = $marxTime->numberFormater($tracks_count, 1);
            // $PTMPL['tracks_link'] = cleanUrls($SETT['url'].'/index.php?page=listen&to=artist&artist='.$rows['uid']);

            // $follower_count = $databaseCL->fetchFollowers($rows['uid'], 1)[0]['counter'];
            // $PTMPL['follower_count'] = $marxTime->numberFormater($follower_count, 1);
            // $PTMPL['followers_link'] = cleanUrls($SETT['url'].'/index.php?page=follow&get=followers&artist='.$rows['uid']);
            // $PTMPL['follow_btn'] = clickFollow($rows['uid'], $user['uid']);

            // $PTMPL['prof_link'] = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$rows['username']);
            // $PTMPL['prof_name'] = $framework->realName($rows['username'], $rows['fname'], $rows['lname']);
            // $PTMPL['prof_photo'] = getImage($rows['photo'], 1);
            // $PTMPL['verif_badge'] = $rows['verified'] ? ' verifiedUserBadge' : '';

            // $suggested .= $template->make();
            $suggested .= smallUser_Card($rows['uid'], null, 1);
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
           $rtn = ' Extended Album';
        } elseif ($r_audio_count > 5 && $r_audio_count < 13) {
           $rt = 3;
           $ic = '<span class="mr-4"><i class="ion-ios-albums"></i> Album</span>';
           $rtn = ' Album';
        } elseif ($r_audio_count > 1 && $r_audio_count < 6) {
           $rt = 2;
           $ic = '<span class="mr-4"><i class="ion-ios-clock"></i> EP</span>';
           $rtn = 'EP';
        } else {
           $rt = 1;
           $ic = '<span class="mr-4"><i class="ion-ios-disc"></i> Single</span>';
           $rtn = 'Single';
        }
        if ($type == 1) {
            return $ic;
        } elseif ($type == 2) {
            return $rtn;
        } elseif ($type == 3) {
            return array('count' => $rt, 'type' => $rtn, 'html' => $ic);
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
    $PTMPL['release_button'] = $progress !== $progress ? 
    '<a href="'.$release_publ_url.'" class="btn btn-success float-right mx-2">'.$LANG['publish_release'].'</a>' : '<a href="'.$release_home_url.'" class="btn btn-primary float-right mx-2">'.$LANG['complete_release'].'</a>'; 

    $PTMPL['delete_button'] = $get_release['status'] == 1 ? 
    '<a href="" title="'.$LANG['delete_permanent'].'"><i class="fa fa-trash float-right m-3 text-white fa-3"></i></a>' : '';

    $PTMPL['edit_button'] = $get_release['status'] == 3 ? 
    '<a href="'.$release_home_url.'" title="'.$LANG['change_meta'].'"><i class="fa fa-edit float-right m-3 text-white fa-3"></i></a>' : '';

    $PTMPL['remove_button'] = $get_release['status'] == 3 ? 
    '<a href="'.$release_remove_url.'" title="'.$LANG['remove_from_sale'].'"><i class="fa fa-times-circle float-right m-3 text-white fa-3"></i></a>' : '';

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
            <span>'.$LANG['view_tracklist'].'</span>
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
                <span role="title">'.$LANG['title_of_track'].'</span> 
                <span role="isrc">'.$LANG['isrc'].'</span> 
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

function showMore_button($type = 0, $item_id = null, $x='More', $sp = 0) {
    global $PTMPL, $LANG, $SETT, $configuration, $user, $framework, $databaseCL, $marxTime, $page_name;
    $download = $un_playlist = $t_id = $public_status = '';

    $databaseCL->track = $t_id = $item_id;
    $track = $type == 1 ? $databaseCL->fetchTracks(null, 2)[0] : ($type == 2 ? $databaseCL->fetchAlbum($item_id)[0] : $databaseCL->fetchPlaylist($item_id)[0]);
    $creator = $type == 1 ? $track['artist_id'] : $track['by'];
    $artist = $framework->userData($creator, 1);
    $artist_name = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
    $page = getPage($page_name);

    // Show the add to playlist link
    $playlister = ($user ? ' 
        <li>
          <a href="#"
            class="playlist-modal-show"
            id="playlist-modal-show"
            data-toggle="modal" 
            data-target="#playlistModal"
            onclick="playlist_modal(2, {\'action\': \'a2list\', \'track\': \''.$t_id.'\', \'type\': '.$type.'})">
            <i class="ion-ios-add-circle-outline"></i> '.$LANG['add_to_playlist'].'
          </a>
        </li>' : '');

        $public_status = $sp ? ($track['public'] ? '<i class="fa fa-globe mx-2"></i>' : '<i class="fa fa-user mx-2"></i>') : '';

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
    } elseif ($type == 3) {
        $track_link = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist='.$track['plid']); 

        // Show the edit playlist button
        $playlister = ($user ? ' 
            <li>
              <a href="#"
                class="playlist-modal-show"
                id="playlist-modal-show"
                data-toggle="modal" 
                data-target="#playlistModal"
                onclick="playlist_modal(2, {\'action\': \'edlist\', \'track\': \''.$t_id.'\', \'type\': '.$type.'})">
                <i class="fa fa-edit"></i> '.$LANG['edit_playlist'].'
              </a>
            </li>' : '');
    }
    if ($page == 'playlist' && $type != 3) {
        $playlist = $databaseCL->fetchPlaylist($_GET['playlist'])[0];
        $plst = $playlist['id'];
        $un_playlist = '
        <li><a href="#" onclick="deleteItem({\'type\': \'1\', \'action\': \'pl_entry\', \'track\': \''.$t_id.'\', \'pl\': \''.$plst.'\'})"><i class="ion-ios-remove-circle-outline"></i> Remove from playlist</a></li>';
    }
    $access = allowAccess($creator);
    $delete = $access && $type != 3 ? '
    <li><a href="#"><i class="ion-ios-trash mx-2"></i> Delete</a></li>' : '';

    $dropdown = '
    <span class="dropdown mx-3" style="text-transform: none; letter-spacing: normal; font-size: unset;">
        <button class="button-light more" type="button" id="moreDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" title="More Options">
            <i class="ion-ios-more"></i> '.$x.'
        </button>
        <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="moreDropdown"> 
            '.$playlister.'
            '.$un_playlist.'
            <li>
                <a href="#"id="copyable" data-clipboard-text="'.$track_link.'">
                    <i class="fa fa-copy mx-2"></i> '.$LANG['copy_link'].'
                </a>
            </li>
            '.$download.'
            '.$delete.'
        </ul>
        '.$public_status.'
    </span>';
    $databaseCL->track = null;
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
    $follows = $databaseCL->fetchFollowers($leader_id, 3); 
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
    $databaseCL->leader_id = $databaseCL->follower_id = null;
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
    global $SETT, $LANG, $framework, $configuration, $databaseCL, $marxTime;
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
                <span class="sidebarHeader__more pc-type-h3">'.$LANG['view_all'].'</span>
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
        $card .= notAvailable($LANG['no_followers'], 'no-padding ');
    } 
    $databaseCL->limit = null;
    return $followership ? $card : '';
}

function showPlaylist($id = null) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 

    $databaseCL->order = ' ORDER BY RAND() LIMIT 12'; 
    $databaseCL->filter = ' OR `playlist`.`featured` = \'1\' AND `playlist`.`public` = \'1\'';
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
    $databaseCL->filter = $databaseCL->limit = null;
    return $show_playlists;
}

function smallUser_Card($id, $button = null, $extra = null) {
    global $SETT, $PTMPL, $user, $framework, $databaseCL, $marxTime;

    $template = new themer('explore/special_users_cards'); $section = ''; 

    $artist = $framework->userData($id, 1);

    $PTMPL['follow_btn_card'] = !$button ? clickFollow($artist['uid'], $user['uid']) : $button;

    $tracks_count = $databaseCL->fetchTracks($artist['uid'], 3)[0]['counter'];
    $PTMPL['tracks_count'] = $marxTime->numberFormater($tracks_count, 1);
    $PTMPL['tracks_link'] = cleanUrls($SETT['url'].'/index.php?page=listen&to=artist&artist='.$artist['uid']);

    $follower_count = $databaseCL->fetchFollowers($artist['uid'], 1)[0]['counter'];
    $PTMPL['follower_count'] = $marxTime->numberFormater($follower_count, 1);
    $PTMPL['followers_link'] = cleanUrls($SETT['url'].'/index.php?page=follow&get=followers&artist='.$artist['uid']);

    $PTMPL['prof_link'] = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$artist['username']);
    $PTMPL['prof_name'] = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
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
    $artist_name = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
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

        $realName = $framework->realName($artist['username'], $artist['fname'], $artist['lname']);
        $card .= '
        <div class="album">
            <div class="album__info">
                <a href="'.$link.'">
                    <div class="project__stems__art">
                        <img src="'.getImage($artist['photo'], 1).'" alt="'.$realName.'" />
                    </div>
                </a>
                <div class="album__info__meta">
                    <div class="album__year"> JOINED '.date('d M Y', strtotime($colaber['time'])).'</div>
                    <div class="album__name">
                        <a href="'.$link.'">'.$realName.'</a>
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
            <img src="'.getImage($artist['photo'], 1, 1).'" alt="'.$framework->realName($artist['username'], $artist['fname'], $artist['lname']).'" />
        </span>
        <span class="related-artist__name">'.$framework->realName($artist['username'], $artist['fname'], $artist['lname']).'</span>
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
                '.$enter_btn.' By '.$framework->realName($creator['username'], $creator['fname'], $creator['lname']).'
            </div>
        </div>
    </div>';
 
    return $cards;
}    

function showNotifications($type = null, $msgs = null, $only_paging = null) {        
    global $PTMPL, $LANG, $SETT, $configuration, $admin, $user, $user_role, $framework, $databaseCL, $marxTime; 

    if ($msgs) {
        $messaging = new social; 
        $messaging->seen = 0;
        $messaging->limit = $configuration['notification_limit'];
        $notifications = $messaging->fetchMessages(6);
    } else {
        $framework->all_rows = $databaseCL->fetchNotifications();
        $PTMPL['pagination'] = $pagination = $framework->pagination();
        $notifications = $databaseCL->fetchNotifications();
    }

    if ($notifications) {
        $notification_list = '';
        foreach ($notifications as $new) {

            if ($msgs) {
                $new = $framework->dbProcessor(sprintf("
                    SELECT * FROM messenger, users WHERE `sender` = '%s'
                    AND `messenger`.`sender` = `users`.`uid`", $new['sender']
                ), 1)[0]; 
                $identifier = 'message_';
                $icon = 'envelope';
                $icon_color = ' text-primary';
                $by = $framework->userData($new['uid'], 1);
                $by_url = $by_profile = ''; 
                $msg_query = $new['thread'] ? '&cid='.$new['cid'].'&r_id='.$new['sender'].'&thread='.$new['thread'] : '&r_id='.$new['sender'];
                $action_var_url  = cleanUrls($SETT['url'] . '/index.php?page=account&view=messages'.$msg_query.'#'.$identifier.$new['cid']); 
                $msg = $framework->myTruncate($framework->rip_tags($new['message']), 80);
                $action = $action_var = sprintf($LANG['new_message_from'], ucfirst($by['username']), $msg);
                $_state = $new['seen'] ? 'span' : 'b';   
                $new['id'] = $new['cid'];        
            } else {
                $identifier = 'notification_';
                $databaseCL->track = $new['object'];

                if ($new['type'] == 1 || $new['type'] == 3) {
                    // Show track links
                    $track = $databaseCL->fetchTracks(null, 2)[0];
                    $track_title = ucfirst($track['title']);
                    $track_url = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$track['safe_link']);
                    $track_link = '<a href="'.$track_url.'#comments">'.ucfirst($track_title).'</a>';
                } else {
                    // Show album links
                    $album = $databaseCL->fetchAlbum($new['object'])[0];
                    $track_title = ucfirst($album['title']);
                    $track_url = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$album['safe_link']);
                    $track_link = '<a href="'.$track_url.'#comments">'.ucfirst($track_title).'</a>';
                } 
                $_state = $new['status'] ? 'span' : 'b';

                $by = $framework->userData($new['by'], 1);
                $by_url = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$by['username']);
                $by_profile = '<a href="'.$by_url.'">'.ucfirst($by['username']).'</a>';
                $action_var_url = $track_url;
                if ($new['type'] == 0) {
                    $icon = 'users';
                    $icon_color = ' text-info';
                    $action = sprintf($LANG['follow_notice'], $by_profile);
                    $action_var = sprintf($LANG['follow_notice'], ucfirst($by['username']));
                    $action_var_url = $by_url;
                } elseif ($new['type'] == 1 || $new['type'] == 2) {
                    $icon = 'heart';
                    $icon_color = ' text-danger';
                    $sub = $new['type'] == 1 ? $LANG['track'] : $LANG['album'];
                    $action = sprintf($LANG['liked_notice'], $by_profile, $sub, $track_link);
                    $action_var = sprintf($LANG['liked_notice'], ucfirst($by['username']), $sub, $track_title);
                } elseif ($new['type'] == 3 || $new['type'] == 4) {
                    $icon = 'comment';
                    $icon_color = ' text-success';
                    $sub = $new['type'] == 3 ? $LANG['track'] : $LANG['album'];
                    $action = sprintf($LANG['comment_notice'], $by_profile, $sub, $track_link);
                    $action_var = sprintf($LANG['comment_notice'], ucfirst($by['username']), $sub, $track_title);
                } else {
                    $icon = 'bullhorn';
                    $icon_color = ' text-warning';
                    $action_var_url = cleanUrls($SETT['url'] . '/index.php?page=account&view=notifications#'.$identifier.$new['id']);
                    $action = $action_var = $new['content'];
                }
            }

            if ($type == 1) {
                $notification_list .= '
                <a href="'.$action_var_url.'" id="nav_'.$identifier.$new['id'].'">
                    <li>
                        <div class="mx-2">
                            '.$action_var.'
                            <br><i class="fa '.icon(3, $icon).$icon_color.'"></i> <'.$_state.'>'.$marxTime->timeAgo($new['date']).'</'.$_state.'>
                        </div> 
                    </li>
                </a>';
            } else {
                $notification_list .= '
                <div class="project_details mb-3" id="notification_'.$new['id'].'">
                    <div class="d-flex justify-content-between">
                        <div class="project_details__text text-justify">
                            '.$action.'
                            <br><i class="fa '.icon(3, $icon).$icon_color.'"></i> <'.$_state.'>'.$marxTime->timeAgo($new['date']).'</'.$_state.'> 
                        </div>
                        <a class="pointer ml-4 text-danger" data-toggle="tooltip" title="Delete this notification" data-placement="left" onclick="firstDelete({type: 1, action: \'notification\', id: '.$new['id'].'})"><i class="fa fa-times-circle fa-2x"></i></a>
                    </div>
                </div>';
            }
        }

        return $notification_list;
    }
}

/* 
* Set the extra form fields for login
*/
function extra_fields() {
    global $SETT, $PTMPL, $LANG, $configuration, $referrer;

    $fbconnect = $recaptcha = $invite_code = $phone_number = '';
    if($configuration['fbacc']) {
        // Generate a session to prevent CSFR attacks
        if (!isset($_SESSION['state'])) {
            $_SESSION['state'] = md5(uniqid(rand(), TRUE)); 
        }
        // Facebook Login Url
        $fbconnect = '<a class="btn btn-fb" href="https://www.facebook.com/dialog/oauth?client_id='.$configuration['fb_appid'].'&redirect_uri='.$SETT['url'].'/connection/connect.php?facebook=true&state='.$_SESSION['state'].'&scope=public_profile,email">Facebook <i class="fa fa-facebook ml-1"></i></a>';
    }
    $captcha_url = '/includes/vendor/goCaptcha/goCaptcha.php?gocache='.strtotime('now');
    if($configuration['captcha']) {
        // Captcha
        $recaptcha = ' 
        <label for="recaptcha_div">'.$LANG['recaptcha'].'</label>
        <div class="d-flex mb-4" id="recaptcha_div"> 
            <input name="recaptcha" type="text" id="recaptcha2" class="form-control mr-5" autocomplete="off">
            <span class="ml-2" id="recaptcha-img"><img width="auto" height="35px" src="'.$SETT['url'].$captcha_url.'" /></span>
        </div> ';
    }  
    if ($configuration['invite_only']) {
        $invite_code_post = (isset($_POST['invite_code'])) ? $_POST['invite_code'] : '';
        $info = ($configuration['fbacc']) ? '<small class="d-flex justify-content-center red-text border grey lighten-4 p-1">'.$LANG['invite_only_info'].'</small>' : '';
        $invite_code = '
        '.$info.'
        <div class="md-form form-sm mb-3">
            <i class="fa fa-key prefix"></i>
            <input name="invite_code" type="text" id="invite_code" class="form-control form-control-sm" autocomplete="off" value="'.$invite_code_post.'">
            <label for="invite_code">'.$LANG['invite_code'].'</label> 
        </div>';
    }
    if ($configuration['activation'] == 'phone') { 
        $_phone = isset($_POST['phone']) ? $_POST['phone'] : '+';
        $phone_number = ' 
        <div class="md-form form-sm mb-3">
            <i class="fa fa-phone prefix"></i>
            <input name="phone" type="text" id="phone" class="form-control form-control-sm" value="'.$_phone.'">
            <label for="phone">'.$LANG['phone_number'].'</label> 
        </div>';
    } 

    $fields =  
        array('fbconnect' => $fbconnect, 'recaptcha' => $recaptcha, 'invite_code' => $invite_code,
            'phone_number' => $phone_number);
    return $fields;
}
