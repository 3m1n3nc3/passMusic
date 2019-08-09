<?php 
require_once(__DIR__ .'/../includes/autoload.php');

  // Get More tracks
  if ($_POST['type'] == 0) {
    $databaseCL->personal_id = $personal = $_POST['personal'];
    $databaseCL->last_id = $_POST['last_track'];
    $track_list = $databaseCL->fetchTracks($_POST['artist'], 3);

    // Count all the associated records
    $databaseCL->counter = $_POST['personal'] ? '' : " AND tracks.public = '1'";
    $count = $databaseCL->fetchTracks($_POST['artist'], 3)[0]; 

    $list_tracks = $more_btn = '';

    if (is_array($track_list) && COUNT($track_list)>0) {
      $last_track = array_reverse($track_list)[0]['id'];
      $n = 0;
      foreach ($track_list as $rows) {
        $n++;
        $list_tracks .= trackLister($rows, $n, 1);
      } 

      // Set the more button
      $more_btn = $count['counter'] > $configuration['page_limits'] ? '<button onclick="loadMore($(this))" data-last-type="0" data-last-personal="'.$personal.'"data-last-artist="'.$_POST['artist'].'" data-last-track="'.$last_track.'" class="show-more button-light" id="load-more">Load More</button>' : '';
    }
    $results = $list_tracks; 
  }


  $data = array("result" => $results, "more" => $more_btn);
  echo json_encode($data, JSON_UNESCAPED_SLASHES);

?>
