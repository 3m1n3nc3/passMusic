<?php 

use wapmorgan\Mp3Info\Mp3Info;

function messageNotice($str, $type = null) {
    switch ($type) {
        case 1:
            $alert = 'success';
            break;

        case 2:
            $alert = 'warning';
            break;

        case 3:
            $alert = 'danger';
            break;

        default:
            $alert = 'info';
            break;
    }
    $string = "
    <div style='padding: 2px;' class='alert alert-" . $alert . "'> " . $str . " </div>";
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
        if (file_exists($_dir.$image)) {
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
        if (file_exists($_dir.$image)) {
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
    if (file_exists($_source)) {
        if ($t) {
            return $_source;    
        }
        return $source = $SETT['url'] . '/uploads/audio/' . $source;    
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
 
function deleteFile($type, $name, $x = null) {
    global $framework;

    if ($type == 0) {
        $ext = 'png';
        $path = 'img';
    } elseif ($type == 1) {
        $ext = 'mp4';
        $path = 'videos';
    }

    $cd = $x ? getcwd() : '..';

    if ($name !== 'default.' . $ext) {
        if (file_exists($cd . '/uploads/' . $path . '/' . $name)) {
            unlink($cd . '/uploads/' . $path . '/' . $name);
            return 1;
        }
    }
    return null;
}

function notAvailable($string, $pad='') {
    return
    '<div class="tracker trackless song-container text-center">
        <div class="'.$pad.'pad-section">  
            <i class="ion-ios-help-circle-outline"></i>
            <p class="small para">' . $string . '</p> 
        </div>
    </div>';
} 

function globalTemplate($type) {
    global $LANG, $SETT, $PTMPL, $contact_, $configuration, $framework, $user, $user_role;
    if ($type == 1) {
        $theme = new themer('homepage/header');
        $section = '';
    } elseif ($type == 2) {
        $theme = new themer('homepage/player');
        $section = '';
    } elseif ($type == 3) {
        $theme = new themer('homepage/sidebar');
        $section = '';
    } elseif ($type == 4) {
        $theme = new themer('homepage/right_sidebar');
        $section = '';
    } else {
        $theme = new themer('container/footer');
        $section = '';
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
    <button type="button" class="close" aria-label="Close" onclick="modal_destroyer(\''.$modal.'Modal\')">
      <span aria-hidden="true">&times;</span>
    </button>';

    $title = $title ? 
    '<div class="modal-header">
      <h5 class="modal-title" id="'.$modal.'ModalLabel">'.$title.'</h5>
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

function listTracks($_track, $n, $x=null) {
    global $SETT, $framework, $databaseCL, $marxTime;
    $explicit = $small = '';
    if ($x==1) {
        $small = '_small';
    }
    $duration = '00:00';
    if (getAudio($_track['audio'], 1)) {
        $audio_tags = new Mp3Info(getAudio($_track['audio'], 1));
        $duration = floor($audio_tags->duration / 60).':'.floor($audio_tags->duration % 60);
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
            <a href="'.getAudio($_track['audio']).'" id="download-link" download="'.$framework->safeLinks($artist_name.' '.$_track['title'], 1).'.'.$t_format.'">
                <i class="ion-ios-cloud-download not-added"></i>
            </a>
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
            <a href="'.getAudio($_track['audio']).'" id="download-link-'.$_track['id'].'" download="'.$framework->safeLinks($artist_name.' '.$_track['title'], 1).'.'.$t_format.'">
                <i class="ion-ios-cloud-download not-added"></i>
            </a>
        </div>
        '.$explicit.'
        <div class="track__plays">'.$count_views.'</div>
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
                        <span class="dropdown mx-3">
                            <button class="button-light more" type="button" id="moreDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <i class="ion-ios-more"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="moreDropdown">
                                <li><a href="#">Start Radio</a></li>
                                <li><a href="#">Add to playlist</a></li>
                                <li><a href="#">Delete</a></li>
                                <li><a href="#" id="copyable" data-clipboard-text="'.$link.'">Copy Album link</a></li>
                            </ul>
                        </span>
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
                            <span class="dropdown mx-3">
                                <button class="button-light more" type="button" id="moreDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <i class="ion-ios-more"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="moreDropdown">
                                    <li><a href="#">Start Radio</a></li>
                                    <li><a href="#">Add to playlist</a></li>
                                    <li><a href="#">Delete</a></li>
                                    <li><a href="#" id="copyable" data-clipboard-text="'.$link.'">Copy Album link</a></li>
                                </ul>
                            </span>
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

    $track_list = $card = $title = $art ='';

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
        $_title = ''; 
        $master_list = $databaseCL->fetchPlaylist('slow', 3);
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
            } else {
                $title = $_title.$value['title'];
                $title = '<a href="'.cleanUrls($SETT['url'] . '/index.php?page=explore&sets='.$_set.'&go='.urlencode($value['name'])).'">'.$title.'</a>';
                $databaseCL->genre = $value['name'];
            }
            $top_tracks = $databaseCL->fetchTopTracks($type);

            $_topest = $databaseCL->fetchTopTracks($type)[0];
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
                    <a href="'.$link.'">
                        <div class="media-card__image" style="background-image: url(&quot;'.getImage($rel['photo'], 1, 1).'&quot;);">
                            <i class="ion-ios-open"></i>
                        </div>
                    </a>
                    <a  href="'.$link.'" class="media-card__footer">'.$rel['fname'].' '.$rel['lname'].'</a>
                </div>';
            }
        }
    } elseif ($type == 3) {
        $sel = 'Tracks';
        $related = $databaseCL->fetchRelated($id, 2);
        if ($related) {
            shuffle($related);
            foreach ($related as $rel) {
                $artist = $framework->userData($rel['artist_id'], 1);
                $link = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$rel['safe_link']);
                $card .= '
                <a href="'.$link.'" class="related-artist related-artist__var">
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
    return sprintf($carder, $link, getImage($rows['photo'], 1, 1), $link, $rows['fname'], $rows['lname']);
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
                        <div class="artist__name">'.$rows['title'].' by '.$rows['fname'].' '.$rows['lname'].'</div>
                    </a>
                    <div class="artist__followers">  
                        <a href="#" title="'.$subcribers_count_full.' Subscribers" class="mr-3"> <i class="ion-ios-people ui ml-3"></i> <span id="subscribers-count-'.$rows['id'].'">'.$subcribers_count.'</span></a>
                        <a href="#" title="'.$count_tracks.' Tracks" class="mr-3"> <i class="ion-ios-disc ui ml-3"></i> '.$count_tracks.'</a>
                    </div>
                    '.$subscribe_btn.'
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
            <a class="hidden__item"href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$track['safe_link'].'&id='.$track['id']).'" id="song-url'.$track['id'].'"> <div id="song-name'.$track['id'].'">'.$track['title'].'</div></a>  
            <a class="hidden__item" href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$username).' " id="song-author'.$track['id'].'"> '.$artist_name.' </a>
        </div>

        <div class="latest-release__song">
            <div class="latest-release__song__title__">'
                .$artist_name.' - '.$track['title'].'
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
    } else {
        $page = 'homepage';
    }
    return $page;
}

function secondaryNavigation($user_id) {
    global $SETT, $user, $framework, $databaseCL; 
    $artist = $framework->userData($user_id, 1);
    $followers = cleanUrls($SETT['url'] . '/index.php?page=follow&get=followers&artist='.$artist['uid']);
    $tracks = cleanUrls($SETT['url'] . '/index.php?page=listen&to=tracks&artist='.$artist['uid']);
    $albums = cleanUrls($SETT['url'] . '/index.php?page=listen&to=albums&artist='.$artist['uid']);
    $artists = cleanUrls($SETT['url'] . '/index.php?page=view_artists&artist='.$artist['uid']);
    $home = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']);
    $playlists = cleanUrls($SETT['url'] . '/index.php?page=playlist&playlist=list&creator='.$artist['uid']);


    $linkers = array('profile' => $home, 'albums' => $albums, 'tracks' => $tracks, 'playlist' => $playlists, 'artists' => $artists, 'followers' => $followers);
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
    $theme = new themer('explore/suggested_users'); $section = ''; 

    // Fetch suggested users 
    $related = [];
    $default = $databaseCL->fetchFollowers($artist_id);
    if ($default) {
        $last_follow = array_reverse($default)[0];
        $databaseCL->fname = $last_follow['fname'];
        $databaseCL->lname = $last_follow['lname'];
        $databaseCL->label = $last_follow['label'];
        $databaseCL->limit = $configuration['sidebar_limit'];
        $related = $databaseCL->fetchRelated($last_follow['uid'], 1);
    }
    if ($related) { 
        shuffle($related);
        $suggested = '';
        foreach ($related as $rows) { 
            $template = new themer('explore/suggested_users_cards'); $section = '';
            $tracks_count = $databaseCL->fetchTracks($rows['uid'], 3)[0]['counter'];
            $PTMPL['tracks_count'] = $marxTime->numberFormater($tracks_count, 1);
            $PTMPL['tracks_link'] = cleanUrls($SETT['url'].'/index.php?page=listen&to=artist&artist='.$rows['uid']);

            $follower_count = $databaseCL->fetchFollowers($rows['uid'], 1)[0]['counter'];
            $PTMPL['follower_count'] = $marxTime->numberFormater($follower_count, 1);
            $PTMPL['followers_link'] = cleanUrls($SETT['url'].'/index.php?page=follow&get=followers&artist='.$rows['uid']);
            $PTMPL['follow_btn'] = clickFollow($rows['uid'], $user['uid']);

            $PTMPL['profile_link'] = cleanUrls($SETT['url'].'/index.php?page=artist&artist='.$rows['username']);
            $PTMPL['profile_name'] = $rows['fname'] . ' ' . $rows['lname'];
            $PTMPL['profile_photo'] = getImage($rows['photo'], 1);
            $PTMPL['verified_badge'] = $rows['verified'] ? ' verifiedUserBadge' : '';

            $suggested .= $template->make();
        }     
        $PTMPL['suggested_users'] = $suggested;  
    } 

    $suggestions = $theme->make();
    return $suggestions;
}    

function sidebar_trackSuggestions($artist_id) {
    global $LANG, $SETT, $PTMPL, $user, $configuration, $framework, $databaseCL, $marxTime; 
    $theme = new themer('explore/suggested_tracks'); $section = ''; 
 
    $track_likes = $databaseCL->listLikedItems($artist_id, 2);
    if ($track_likes) {
        $PTMPL['total_likes'] = $marxTime->numberFormater($track_likes[0]['likes']);
        $PTMPL['likes_link'] = cleanUrls($SETT['url'] . '/index.php?page=listen&to=tracks&artist='.$artist_id);
        shuffle($track_likes);
        $suggested = '';
        foreach ($track_likes as $rows) {
            $template = new themer('explore/suggested_tracks_cards'); $section = '';
            $artist = $framework->userData($rows['artist_id'], 1);
            $PTMPL['profile_link'] = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']);
            $PTMPL['profile_name'] = $artist['fname'] . ' ' . $artist['lname'];
            $PTMPL['track_link'] = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$rows['safe_link']);
            $PTMPL['likes_link'] = cleanUrls($SETT['url'] . '/index.php?page=track&track='.$rows['safe_link'].'&likes=view');
            $PTMPL['track_title'] = $rows['title'];
            $PTMPL['track_art'] = getImage($rows['art'], 1); 
            $PTMPL['track_id'] = $rows['id'];
            $PTMPL['track_audio'] = getAudio($rows['audio']);
            $PTMPL['track_format'] = strtolower(pathinfo($rows['audio'], PATHINFO_EXTENSION));

            $likes = $databaseCL->userLikes($user['uid'], $rows['id'], 2); // check if this track is liked
            $PTMPL['like_button'] = clickLike(2, $rows['id'], $user['uid'], null);
            $PTMPL['like_title'] = $likes ? 'Unlike '.$rows['title'] : 'Like '.$rows['title'];
            $count_likes = $databaseCL->LikesCount(3, $rows['id'])[0];
            $count_views = $databaseCL->fetchStats(1, $rows['id'])[0];
            $PTMPL['likes_count'] = $marxTime->numberFormater($count_likes['total']);
            $PTMPL['views_count'] = $marxTime->numberFormater($count_views['total']);
            $PTMPL['likes_count_full'] = $marxTime->numberFormater($count_likes['total'], 1);
            $PTMPL['views_count_full'] = $marxTime->numberFormater($count_views['total'], 1);

            $suggested .= $template->make();
        }
    }
    $PTMPL['suggested_track_cards'] = $suggested; 
    $suggestions = $theme->make();
    return $suggestions;
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

    $div_button = '<div data-like-id="'.$item_id.'" data-type="'.$type.'" class="dolike" id="doLike_'.$item_id.'"><i class="ion-ios-heart'.$e.$hover.' border'.$liked.'"></i></div>'; 

    $button = $x == 1 ? $button : $div_button; 
    return $user ? $button : '';
}

function clickSubscribe($playlist, $user_id, $x = 1) {
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
    } elseif ($type == 2) {
        $rel = 'Following';
    }
    $card = '';
    $c = 0; 
    if ($followership) {
        $card = '
        <div class="section-title">'.$rel.' (<span id="'.strtolower($rel).'-count-'.$user_id.'">'.$follower_count.'</span>)</div> 
        <div class="related-artists pb-3">';
        foreach ($followership as $rows) { 
            $c++ ;
            if ($c == 11) {
                break;
            }
            $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$rows['username']);
            $card .= '
            <a href="'.$link.'" class="related-artist">
                <span class="related-artist__img">
                    <img src="'.getImage($rows['photo'], 1, 1).'" alt="'.$rows['fname'].' '.$rows['lname'].'" />
                </span>
                <span class="related-artist__name">'.$rows['fname'].' '.$rows['lname'].'</span>
            </a>';
        }
        $card .= '
        </div>';
    } else {
        $card .= notAvailable('No Followers', 'no-padding ');
    } 
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
