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

function getImage($image, $type = null, $a = null, $b = null) {
    // $a = 1: Get direct link to image
    global $SETT;
    if (!$image) {
      $dir = $SETT['url'] . '/uploads/img/';
      $image = 'default.png';
    }

    $c = null;
    if ($type == 1) {
      // Deletable images
      $dir = $SETT['url'] . '/uploads/photos/';
      $_dir = 'uploads/photos/';
      $c = 1;
    } elseif ($type == 2) {
      // More Site specific images
      $dir = $SETT['url'] . '/' . $SETT['template_url'] . '/images/';
      $_dir = $SETT['template_url'] . '/images/';
    } else {
      // Site specific images
      $dir = $SETT['url'] . '/' . $SETT['template_url'] . '/img/';
      $_dir = $SETT['template_url'] . '/img/';
    } 

    // Get the directory
    $cd = $b ? getcwd() : '';
    $cd_image = $cd . $_dir . $image;

    // Show the image
    if ($a == 2) {
        $image = $a ? $dir.$image : $cd_image;
        if (@exif_imagetype($image)) {
          $image = $image;
        } else {
            $image = $SETT['url'] . '/uploads/photos/default.png';
        }
    } else {
        if (file_exists($cd_image)) {
          $image = $a ? $dir.$image : $cd_image;
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
    if ($t) {
        return 'uploads/audio/' . $source;
    }
    return $source = $SETT['url'] . '/uploads/audio/' . $source;
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
    global $SETT, $framework;
    $explicit = $small = '';
    if ($x==1) {
        $small = '_small';
    }

    $audio_tags = new Mp3Info(getAudio($_track['audio'], 1));
    $duration = floor($audio_tags->duration / 60).':'.floor($audio_tags->duration % 60);
    
    if ($_track['explicit']) {
        $explicit = '
            <div class="track__explicit">
              <span class="label">Explicit</span>
            </div>';
    }
    $artist = $framework->userData($_track['artist_id'], 1);
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $t_format = strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION));
    $track = '

    <div class="tracker song-container" id="track'.$_track['id'].'">
        <div class="track__number">'.$n.'</div>
        <div class="track__added subst">
            <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="play'.$_track['id'].'" data-track-url="'.getAudio($_track['audio']).'" data-track-format="'.strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION)).'" data-hideable="1" class="track song-play-btn fa fa-play-circle ">
            </div>
        </div>
        <div class="song-art">
            <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link']).'&id='.$_track['id'].'" rel="loadpage"><img src="'.getImage($_track['art'], 1).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'"></a>
        </div>
        <div class="track__title'.$small.'">
            <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&id='.$_track['id']).'" id="song-url'.$_track['id'].'"> <div id="song-name'.$_track['id'].'">'.$_track['title'].'</div></a>
        </div>
        <div class="track__author'.$small.'" id="song-author">
            <a href="'.cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']).' " id="song-author'.$_track['id'].'"> '.$artist_name.' </a>
        </div>
        '.$explicit.'
        <div class="track__length">'.$duration.'</div>
        <div class="track__popularity">
            '.number_format($_track['views']).'
        </div>
    </div> '; 
    return $track;
}

function trackLister($_track, $n, $x=null) {
    global $SETT, $framework;
    $artist = $framework->userData($_track['artist_id'], 1);
    $artist_name = $artist['fname'].' '.$artist['lname'];
    $explicit = $_track['explicit'] ? '
    <div class="track__explicit">
        <span class="label">Explicit</span>
    </div>' : '';

    $list = '
    <div class="song-container" id="track'.$_track['id'].'">
        <div class="trackitem" id="trackitem'.$_track['id'].'">
            <div data-track-name="'.$_track['title'].'" data-track-id="'.$_track['id'].'" id="play'.$_track['id'].'" data-track-url="'.getAudio($_track['audio']).'" data-track-format="'.strtolower(pathinfo($_track['audio'], PATHINFO_EXTENSION)).'"  data-hideable="0" class="track">
                <div class="tracklist__bg_art" style="background-image: url('.getImage($_track['art'], 1, 2).')">
                    <i class="ion-ios-play" id="icon_play'.$_track['id'].'"></i> 
                    <img style="display: none;" src="'.getImage($_track['art'], 1, 2).'" id="song-art'.$_track['id'].'" alt="'.$_track['title'].'">
                </div>
            </div>
            <div class="track__added">
                <i class="ion-ios-add-circle not-added"></i>
            </div>
            <div class="track__title">
                <span id="song-author'.$_track['id'].'">'.$artist_name.'</span>
                <a href="'.cleanUrls($SETT['url'] . '/index.php?page=track&track='.$_track['safe_link'].'&id='.$_track['id']).'" id="song-url'.$_track['id'].'"> <div id="song-name'.$_track['id'].'">'.$_track['title'].'</div>
            </a>
        </div>
        <div class="track__download">
            <a href="'.getAudio($_track['audio']).'" id="download-link" download="'.$framework->safeLinks($artist_name.' '.$_track['title'], 1).'">
                <i class="ion-ios-cloud-download not-added"></i>
            </a>
        </div>
        '.$explicit.'
        <div class="track__plays">'.number_format($_track['views']).'</div>
    </div>
    </div> ';
    return $list;
}

function artistAlbums($artist) {
    global $SETT, $user, $framework, $databaseCL;
    $albums = $databaseCL->fetchAlbum($artist, 1);

    $card = '';
    if ($albums) {
        foreach ($albums as $rows) {

            $databaseCL->user_id = $user['uid'];
            $get_tracks = $databaseCL->albumEntry($rows['id']);
            $link = cleanUrls($SETT['url'] . '/index.php?page=album&album='.$rows['safe_link']);

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
                            <button class="button-light save">Save</button>
                            <span class="dropdown">
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
                        <div class="media-card__image" style="background-image: url('.getImage($rel['photo'], 1, 1).');">
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
    global $SETT, $framework, $databaseCL;
    $artist = $framework->userData($artist_id, 1);
    $link = cleanUrls($SETT['url'] . '/index.php?page=artist&artist='.$artist['username']);
    $followers = $databaseCL->fetchFollowers($artist['uid'], 1);
    $count_followers = $followers ? count($followers) . ' Followers' : 'No Followers';
    $card = '
    <div class="artist__card">
        <div class="free-artist">
            <div class="album-artist__">
                <img src="'.getImage($artist['photo'], 1, 1).'" alt="'.$artist['fname'].' '.$artist['lname'].'" />
            </div>
            <a href="'.$link.'">
                <div class="artist__name">'.$artist['fname'].' '.$artist['lname'].'</div>
            </a>
            <div class="artist__followers"><i class="fa fa-users ui"></i> '.$count_followers.'</div>
            <button id="follow_btn" data-folow-id="'.$artist['uid'].'" class="button-dark">
                <i class="ion-ios-person-add"></i> FOLLOW
            </button>
        </div>
    </div>';
    return $card;
}

function mostPopular($artist_id) {
    global $SETT, $framework, $databaseCL;
    $track = $databaseCL->fetchTracks($artist_id, 1)[0];
    $explicit = $track['explicit'] ? '<div class="explicit__label ">Explicit</div>' : '';
    $role = $framework->userRoles('', $artist_id);
    $t_format = strtolower(pathinfo($track['audio'], PATHINFO_EXTENSION));
    $card = '
    <style type="text/css">height: 28px;</style>
    <div class="latest-release">
        <div class="popular-card__image" style="background-image: url('.getImage($track['art'], 1, 1).');">
            <i data-track-name="'.$track['title'].'" data-track-id="'.$track['id'].'" id="play'.$track['id'].'" data-track-url="'.getAudio($track['audio']).'" data-track-format="'.$t_format.'" data-hideable="1" class="track now-waving ion-ios-play-circle">
            </i> 
        </div>
        <div class="latest-release__song">
            <div class="latest-release__song__title__">'
                .$track['fname'].' '.$track['lname'].' - '.$track['title'].'
            </div>
            <div id="waveform'.$track['id'].'"></div>
            <div class="small_track__container">
                '.$explicit.'
                <div class="count-holder">
                    <i class="ion-ios-radio"></i>
                    <span style="font-size: 18px;">'.number_format($track['views']).'</span>
                </div>
            </div>
        </div>
    </div>
    <div id="now-waving" style="display: none;">0</div>
    <div id="real-play'.$track['id'].'" style="display: none;">0</div>
    <div id="wave_init" data-track-url="'.getAudio($track['audio']).'" data-track-id="'.$track['id'].'" data-track-format="'.$t_format.'"></div>';
    return $track ? $card : notAvailable('This '.$role.' has no popular tracks', 'no-padding ');
}

function showFollowers($user_id, $type=null) {
    global $SETT, $framework, $databaseCL;
    // 1: Sidebar followers
    // 0: Inner followers
    $followership = $databaseCL->fetchFollowers($user_id, $type);  
    $card = '';
    $c = 0; 
    if ($followership) {
        $card = '
        <div class="related-artists">';
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
    return $card;
}
