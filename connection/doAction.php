<?php 
require_once(__DIR__ .'/../includes/autoload.php');
  $user['id'] = 1; 

  if ($_POST['type'] == 4) {
    // Check for existing followership
    $do = $delete = 0;
    $sql = sprintf("SELECT subscriber FROM playlistfollows WHERE `playlist` = '%s' AND `subscriber` = '%s'", $_POST['item_id'], $user['id']);
    $check = $framework->dbProcessor($sql, 1)[0];

    $check = $check ? true : false;

    // Do the follow
    if ($check) {
      $sql = sprintf("DELETE FROM playlistfollows WHERE `subscriber` = '%s' AND `playlist` = '%s'", $user['id'], $_POST['item_id']);
        $delete = $framework->dbProcessor($sql, 0, 1);
    } else {
       $sql = sprintf("INSERT INTO playlistfollows (`subscriber`, `playlist`) VALUES ('%s', '%s')", $user['id'], $_POST['item_id']);
        $do = $framework->dbProcessor($sql, 0, 1); 
    }
  } elseif ($_POST['type'] == 3) {
    // Check for existing followership
    $do = $delete = 0;
    $sql = sprintf("SELECT follower_id FROM relationship WHERE `follower_id` = '%s' AND `leader_id` = '%s'", $user['id'], $_POST['item_id']);
    $check = $framework->dbProcessor($sql, 1)[0];

    $check = $check ? true : false;

    // Do the follow
    if ($check) {
      $sql = sprintf("DELETE FROM relationship WHERE `follower_id` = '%s' AND `leader_id` = '%s'", $user['id'], $_POST['item_id']);
        $delete = $framework->dbProcessor($sql, 0, 1);
    } else {
       $sql = sprintf("INSERT INTO relationship (`follower_id`, `leader_id`) VALUES ('%s', '%s')", $user['id'], $_POST['item_id']);
        $do = $framework->dbProcessor($sql, 0, 1); 
    }
  } else {
      // Check for existing likes
      $do = $delete = 0;
      $sql = sprintf("SELECT user_id, item_id FROM %s WHERE `user_id` = '%s' AND `item_id` = '%s' AND `type` = '%s'", $_POST['table'], $user['id'], $_POST['item_id'], $_POST['type']);
      $check = $framework->dbProcessor($sql, 1)[0];

      $check = $check ? true : false;

      // $type == 1: Do Like album or track
      if ($check) {
        $sql = sprintf("DELETE FROM %s WHERE `user_id` = '%s' AND `item_id` = '%s' AND `type` = '%s'", $_POST['table'], $user['id'], $_POST['item_id'], $_POST['type']);
        $delete = $framework->dbProcessor($sql, 0, 1);
      } else { 
        $sql = sprintf("INSERT INTO %s (`type`, `user_id`, `item_id`) VALUES ('%s', '%s', '%s')", $_POST['table'], $_POST['type'], $user['id'], $_POST['item_id']);
        $do = $framework->dbProcessor($sql, 0, 1); 
      }
  }

  if ($do == 1) {
    $status = $msg = 1; 
  } elseif ($delete == 1) {
    $status = $msg = 0; 
  } else {
    $status = 0; 
    $msg = $do; 
  }
  $data = array('status' => $status, 'msg' => $msg);

  echo json_encode($data, JSON_UNESCAPED_SLASHES); 
