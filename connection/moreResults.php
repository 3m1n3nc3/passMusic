<?php 
require_once(__DIR__ .'/../includes/autoload.php');

  $results = $more_btn = $count = '';

  $_POST['type'] = $last_type = $framework->db_prepare_input($_POST['type']);

  if ($last_type == 6) {
    $data = $_POST['data'];
  } else {
    $_POST['personal'] = $personal = $framework->db_prepare_input($_POST['personal']);
    $personal == $user['uid'] ? $databaseCL->personal_id = $personal : '';

    $_POST['last_track'] = $last_track = $framework->db_prepare_input($_POST['last_track']);
    $_POST['artist'] = $last_artist = $framework->db_prepare_input($_POST['artist']);
  }

 
  // Get More tracks
  if ($_POST['type'] == 0) {
    $databaseCL->last_id = $last_track;
    $track_list = $databaseCL->fetchTracks($last_artist, 3);

    // Count all the associated records 
    $databaseCL->counter = true;
    $count = $databaseCL->fetchTracks($last_artist, 3)[0]['counter'];

    $list_tracks = $more_btn = '';

    if ($track_list && $count>0) {
      $last_track = array_reverse($track_list)[0]['id'];
      $n = 0;
      foreach ($track_list as $rows) {
        $n++;
        $list_tracks .= trackLister($rows, $n, 1);
      } 

      // Set the more button
      $more_btn = $count > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="0" data-last-personal="'.$personal.'"data-last-artist="'.$last_artist.'" data-last-track="'.$last_track.'" class="show-more button-light" id="load-more">Load More</button>' : '';
    }
    $results = $list_tracks; 
  } elseif ($_POST['type'] == 1) {
    // Get More liked tracks
    //  
    $databaseCL->last_id = $_POST['last_track']; 
    $track_list = $databaseCL->listLikedItems($_POST['artist'], 2); 

    $list_tracks = $more_btn = '';
    if (is_array($track_list) && COUNT($track_list)>0) {
      $last_track = array_reverse($track_list)[0]['like_id']; 
      $n = 0;

      // Count all the associated records
      $databaseCL->counter = true;
      $count = $databaseCL->listLikedItems($_POST['artist'], 2)[0];
      $more_btn = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="1" data-last-personal="" data-last-artist="'.$_POST['artist'].'" data-last-track="'.$last_track.'" class="show-more button-light" id="load-more">Load More</button>' : '';
      
      foreach ($track_list as $rows) {
        $n++;
        $list_tracks .= trackLister($rows, $n, 1);
        $last_id = $rows['like_id'];
      }
    }
    $results = $list_tracks; 
  } elseif ($_POST['type'] == 2) {
    // Get More liked tracks
    //  
    $databaseCL->last_id = $_POST['last_track'];  
    $album_list = $databaseCL->listLikedItems($_POST['artist'], 1); 

    $list_albums = $more_btn = '';
    if (is_array($album_list) && COUNT($album_list) > 0) {
      $last_album = array_reverse($album_list)[0]['like_id']; 
      $n = 0;

      // Count all the associated records
      $databaseCL->counter = true;
      $count = $databaseCL->listLikedItems($_POST['artist'], 1)[0];
      $more_btn = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="2" data-last-personal="" data-last-artist="'.$_POST['artist'].'" data-last-track="'.$last_album.'" class="show-more button-light" id="load-more">Load More</button>' : '';
      
      foreach ($album_list as $rows) {
        $n++;
        $list_albums .= albumsLister($rows['by'], $rows); 
      }
    }
    $results = $list_albums; 
  } elseif ($_POST['type'] == 3) {
    // Get More liked tracks
    //  
    $databaseCL->last_id = $_POST['last_track'];
    $cl = $databaseCL->userLikes($_POST['artist'], 0, 3);
    $databaseCL->limit = true;
    $liked_artists = $databaseCL->userLikes($_POST['artist'], 0, 3);

    $_artists = $more_btn = '';
    if ($liked_artists) {        
      foreach ($liked_artists as $rows) { 
        $_artists .= '<div class="mb-3">'.artistCard($rows['artist_id']).'</div>';
        $last_track = $rows['artist_id'];
      }
      $more_btn = count($cl) > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="3" data-last-personal="" data-last-artist="'.$_POST['artist'].'" data-last-track="'.$last_track.'" class="show-more button-light" id="load-more">Load More</button>' : '';
    }
    $results = $_artists; 
  } elseif ($_POST['type'] == 4) {
    // Get More follows
    //  
    $type = $_POST['personal'];

    $databaseCL->last_id = $_POST['last_track'];
    $follows_count = $databaseCL->fetchFollowers($_POST['artist'], $type);
    $databaseCL->limit = true;
    $follows = $databaseCL->fetchFollowers($_POST['artist'], $type); 
    $follow_cards = '';
    if ($follows) {
        foreach ($follows as $rows) {
            $follow_cards .= followCards($rows);
            $last_id = $rows['order_id'];
        }
    }
    $more_btn = count($follows_count) > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="4" data-last-personal="'.$type.'" data-last-artist="'.$_POST['artist'].'" data-last-track="'.$last_id.'" class="show-more button-light" id="load-more">Load More</button>' : '';
    $results = $follow_cards;
  } elseif ($_POST['type'] == 5) {
    // Get More track Likers
    //   
    $databaseCL->type = 2;
    $databaseCL->last_time = $last_track;
    $databaseCL->limit = false;
    $cl = $databaseCL->userLikes(null, $last_artist, 5);
    $databaseCL->limit = true;
    $likers = $databaseCL->userLikes(null, $last_artist, 5);
    $_items = '';
    if ($likers) { 
      foreach ($likers as $row) { 
        $_items .= '<div class="mb-3">'.artistCard($row['artist_id']).'</div>';
        $last_items = $row['artist_id'];
      }
      $more_btn = count($cl) > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="5" data-last-personal="" data-last-artist="'.$item_id.'" data-last-track="'.$last_items.'" class="show-more button-light" id="load-more">Load More</button>' : '';
    } 

    $results = $_items;
  } else {
    if ($data['type'] == 1) {
      $lcid = '';
      if (isset($data['last_cid'])) {
        $databaseCL->creator = $data['last_cid'];
        $lcid = $data['last_cid'];
      }
      $databaseCL->last_id = $data['last'];
      $projects = $databaseCL->fetchProject(0, 2);
      $show_projects = '';
      if ($projects) {
        foreach ($projects as $rows) {
            $show_projects .= projectsCard($rows);
        }
        $show_projects .= '<span style="display: none;" class="load-more-container"></span>'; 

        // Count all the associated records
        $databaseCL->counter = true;
        $count = $databaseCL->fetchProject(0, 2)[0]['counter'];
        $more_btn = $count > $configuration['page_limits'] ? '
        <button onclick="loadMore_improved($(this), {type: 1, last: '.$rows['id'].$lcid.'})" class="show-more button-light" id="load-more">Load More</button>' : '';
      }
      $results = $show_projects;
    } elseif ($data['type'] == 2) { 
  
    }
  }

  $data = array("result" => $results, "more" => $more_btn, "left" => $count);
  echo json_encode($data, JSON_UNESCAPED_SLASHES);

?>
