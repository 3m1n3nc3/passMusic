<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$status = $msg = $option = $response = '';

$data = $_POST['data'];
if (is_array($data)) {
  $data = $data;
} else {
  $data = json_decode($data);
}

if ($data['action'] == 'pl_entry') {
  // Remove track from playlist 
  $sql = sprintf("DELETE FROM playlistentry WHERE `track` = '%s' AND `playlist` = '%s'", $data['track'], $data['pl']);
  $do = $framework->dbProcessor($sql, 0, 1);
  if ($do == 1) {
    $status = 1;
    $msg = 'Track removed from playlist';
  } else {
    $status = 0;
    $msg = 'Failed to remove track from playlist! Response: '.$do;
  }
} elseif ($data['action'] == 'release_audio') {
  // Delete release audio files and remove from db
  $release_audio = $databaseCL->fetchRelease_Audio(null, $data['rel_id']);
  if ($release_audio) {
    $del = null;
    foreach ($release_audio as $audio) {echo $data['rel_id'];
      $del = deleteFile($audio['audio'], null, 1);
    }
    if ($del) {
      $sql = sprintf("DELETE FROM new_release_tracks WHERE `release_id` = '%s'", $data['rel_id']);
      $do = $framework->dbProcessor($sql, 0, 1);
    }
  }
}

$data = array('status' => $status, 'msg' => $msg, 'option' => $option, 'resp' => $response);
echo json_encode($data, JSON_UNESCAPED_SLASHES); 
