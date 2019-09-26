<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$status = $msg = $option = $response = '';
$type = $_POST['type'];
$data = $_POST['data'];
if (is_array($data)) {
	$data = $data;
} else {
	$data = json_decode($data);
}

if ($type == 1) { 
	if ($data['action'] == 'c_status') {
		$sql = sprintf("UPDATE projects SET `status` = '%s' WHERE `id` = '%s'", $data['status'], $data['project']);
		$response = $framework->dbProcessor($sql, 0, 1);
		if ($data['status'] == 1) {
			$msg = 'Project has been activated'; 
			$status = 1;
		} else {
			$msg = 'Project has been closed'; 
			$status = 0;
		}
	} elseif ($data['action'] == 'publish') {
		$sql = sprintf("UPDATE projects SET `published` = '%s' WHERE `id` = '%s'", $data['status'], $data['project']);
		$response = $framework->dbProcessor($sql, 0, 1);
		if ($data['status'] == 1) {
			$msg = 'Project has been published';
			$text = 'Unpublish';
			$class = 'warning';
			$status = 0;
		} else {
			$msg = 'Project has been unpublished';
			$text = 'Publish';
			$class = 'success';
			$status = 1;
		}
		$option = '<button type="button" class="btn btn-block btn-'.$class.'" onclick="options(1, {action: \''.$data['action'].'\', project: '.$data['project'].', status: '.$status.'})"> '.$text.' </button>';
	}
} elseif ($type == 2) {
	$errors = array();
	if ($data['action'] == 'c_list') {
		// Show the create playlist form
		$option = playlistManager(2);
	} elseif ($data['action'] == 'a2list') {
		// Show the Add a track to playlist form
		$option = playlistManager(3, $data['track']);
	} elseif ($data['action'] == 'create') {
		// Create a new playlist
		$databaseCL->extra = true;
		$pl = $databaseCL->fetchPlaylist($data['title'])[0];
		if ($data['title'] == '') {
			$errors[] = 'Title can not be empty';
		} elseif ($pl) {
			$errors[] = 'You already have a playlist named "'.$data['title'].'"';
		}
		if (empty($errors) === true) {
			$plid = $framework->token_generator($length = 10);
			
			$sql = sprintf("INSERT INTO playlist (`by`, `title`, `public`, `plid`) VALUES ('%s', '%s', '%s', %s)", $user['uid'], $data['title'], $data['public'], $plid);
  			$do = $framework->dbProcessor($sql, 0, 1);
 
      		if ($do == 1) {
      			$option = messageNotice('Playlist "'.$data['title'].'" Successfully created', 1);
      		} else {
      			$option = $do;
      		}
		} else {
			$option = messageNotice($errors[0], 3);
		}
	} elseif ($data['action'] == 'add') { 
        $al = strpos($data['track'], ',');
        if ($al) {
			// Add album to playlist
        	$list_tracks = explode(',', $data['track']);
        	foreach ($list_tracks as $key => $track) {
				$databaseCL->extra = true;
        		$pl = $databaseCL->playlistEntry($data['playlist'], null, $track)[0];
				if ($pl) {
					$sql = sprintf("UPDATE playlistentry SET `playlist` = '%s', `track` = '%s' WHERE `playlist` = '%s' AND `track` = '%s'", $data['playlist'], $track, $data['playlist'], $track);
				} else {
					$sql = sprintf("INSERT INTO playlistentry (`playlist`, `track`) VALUES ('%s', '%s')", $data['playlist'], $track);
				}
	  			$do = $framework->dbProcessor($sql, 0, 1);
	 			$mssg = messageNotice(count($list_tracks).' Tracks successfully added to playlists', 1);
	      		if (strrpos($do, 'SQLSTATE')) {
		      		$option = $do;
		      	} else {
	      			$option = $mssg;
	      		} 	 
        	}
        } else {
			// Add track to playlist
			$databaseCL->extra = true;
			$pl = $databaseCL->playlistEntry($data['playlist'], null, $data['track'])[0];
			if ($pl) {
				$sql = sprintf("UPDATE playlistentry SET `playlist` = '%s', `track` = '%s' WHERE `playlist` = '%s' AND `track` = '%s'", $data['playlist'], $data['track'], $data['playlist'], $data['track']);
			} else { 
				$sql = sprintf("INSERT INTO playlistentry (`playlist`, `track`) VALUES ('%s', '%s')", $data['playlist'], $data['track']);
			}
  			$do = $framework->dbProcessor($sql, 0, 1);
 
	 		$mssg = messageNotice('Playlist has been updated with new track', 1);
      		if (strrpos($do, 'SQLSTATE')) {
	      		$option = $do;
	      	} else {
      			$option = $mssg;
      		} 
		}
	} 
}


$data = array('status' => $status, 'msg' => $msg, 'option' => $option, 'resp' => $response);
echo json_encode($data, JSON_UNESCAPED_SLASHES); 
