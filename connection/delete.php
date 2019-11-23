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
    foreach ($release_audio as $audio) { 
      $del = deleteFile($audio['audio'], null, 1);
    }
    if ($del) {
      $sql = sprintf("DELETE FROM new_release_tracks WHERE `release_id` = '%s'", $data['rel_id']);
      $do = $framework->dbProcessor($sql, 0, 1);
    }
  }
} elseif ($data['action'] == 'artist') {
  // Delete artist and remove all related items  
    $status = $databaseCL->deleteReleaseArtist($data['id'], 1);
    if ($status == 1) {
      $status = 1;
      $response = 'This user has been deleted successfully';
      $msg = messageNotice($response, 1);
    } else { 
      $response = 'An Error occurred: Unable to delete user';
      $msg = messageNotice($response, 3);
    } 
} elseif ($data['action'] == 'message') {
  $sql = sprintf("DELETE FROM messenger WHERE `cid` = '%s'", $data['id']); 
  $status = $framework->dbProcessor($sql, 0, 1);  
} elseif ($data['action'] == 'notification') {
  $sql = sprintf("DELETE FROM notification WHERE `id` = '%s'", $data['id']); 
  $status = $framework->dbProcessor($sql, 0, 1);  
}

$data = array('status' => $status, 'msg' => $msg, 'option' => $option, 'resp' => $response);
echo json_encode($data, JSON_UNESCAPED_SLASHES); 
