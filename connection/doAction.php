<?php 
require_once(__DIR__ .'/../includes/autoload.php');

  $do = $delete = 0;
  if ($_POST['type'] == 6) {
     // Request access to project
     // 
    $databaseCL->user_id = $_POST['user_id'];
    $check = $databaseCL->fetch_colabRequests($_POST['project'], 1)[0];
    $check = $check ? true : false;

    if (!$check) {
      $sql = sprintf("INSERT INTO collabrequests (`project`, `user`) VALUES ('%s', '%s')", $_POST['project'], $_POST['user_id']); 
      $do = $framework->dbProcessor($sql, 0, 1);
    } else {
      $sql = sprintf("DELETE FROM collabrequests WHERE `project` = '%s' AND `user` = '%s'", $_POST['project'], $_POST['user_id']);
      $framework->dbProcessor($sql, 0, 1);
    }
  } elseif ($_POST['type'] == 5) {
    // Check for existing collabing
 
    $databaseCL->user_id = $_POST['user_id'];
    $check = $databaseCL->fetch_projectCollaborators($_POST['project'], 2)[0];

    $check = $check ? true : false;

    // Do the approval
    if ($check) {
      // Delete the collaborator
      // if a collaborator is deleted, also delete all their private instrumental, or hide the public ones, then delete
      // their stem files
      $sql = sprintf(
        " DELETE FROM collaborators WHERE `project` = '%s' AND `user` = '%s';
          DELETE FROM instrumentals WHERE `project` = '%s' AND `user` = '%s' AND `public` = '0';
          DELETE FROM stems WHERE `project` = '%s' AND `user` = '%s';
          UPDATE instrumentals SET `hidden` = '1' WHERE `project` = '%s' AND `user` = '%s' AND `public` = '1'", 
          $_POST['project'], $_POST['user_id'], $_POST['project'], $_POST['user_id'], $_POST['project'], $_POST['user_id'], $_POST['project'], $_POST['user_id']);
        $delete = $framework->dbProcessor($sql, 0, 1);
    } else {
      $sql = sprintf("INSERT INTO collaborators (`project`, `user`) VALUES ('%s', '%s')", $_POST['project'], $_POST['user_id']);
      $do = $framework->dbProcessor($sql, 0, 1); 
      $sql = sprintf("DELETE FROM collabrequests WHERE `project` = '%s' AND `user` = '%s'", $_POST['project'], $_POST['user_id']);
      $do = $framework->dbProcessor($sql, 0, 1); 
    }
  } elseif ($_POST['type'] == 4) {
    // Check for existing followership
 
    $sql = sprintf("SELECT subscriber FROM playlistfollows WHERE `playlist` = '%s' AND `subscriber` = '%s'", $_POST['item_id'], $user['uid']);
    $check = $framework->dbProcessor($sql, 1)[0];

    $check = $check ? true : false;

    // Do the follow
    if ($check) {
      $sql = sprintf("DELETE FROM playlistfollows WHERE `subscriber` = '%s' AND `playlist` = '%s'", $user['uid'], $_POST['item_id']);
        $delete = $framework->dbProcessor($sql, 0, 1);
    } else {
       $sql = sprintf("INSERT INTO playlistfollows (`subscriber`, `playlist`) VALUES ('%s', '%s')", $user['uid'], $_POST['item_id']);
        $do = $framework->dbProcessor($sql, 0, 1); 
    }
  } elseif ($_POST['type'] == 3) {
    // Check for existing followership
 
    $sql = sprintf("SELECT follower_id FROM relationship WHERE `follower_id` = '%s' AND `leader_id` = '%s'", $user['uid'], $_POST['item_id']);
    $check = $framework->dbProcessor($sql, 1)[0];

    $check = $check ? true : false;

    // Do the follow
    if ($check) {
      $sql = sprintf("DELETE FROM relationship WHERE `follower_id` = '%s' AND `leader_id` = '%s'", $user['uid'], $_POST['item_id']);
        $delete = $framework->dbProcessor($sql, 0, 1);
    } else {

      // Set the notification
      $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `type`) VALUES ('%s', '%s', '0')", $_POST['item_id'], $user['uid']), 0, 1); 

      // Add the follow
      $sql = sprintf("INSERT INTO relationship (`follower_id`, `leader_id`) VALUES ('%s', '%s')", $user['uid'], $_POST['item_id']);

      $do = $framework->dbProcessor($sql, 0, 1); 
    }
  } else {

      // Check for existing likes
      $sql = sprintf("SELECT user_id, item_id FROM %s WHERE `user_id` = '%s' AND `item_id` = '%s' AND `type` = '%s'", $_POST['table'], $user['uid'], $_POST['item_id'], $_POST['type']);

      $check = $framework->dbProcessor($sql, 1)[0];
      $check = $check ? true : false;

      // $type == 1: Do Like album or track
      if ($check) {
        $sql = sprintf("DELETE FROM %s WHERE `user_id` = '%s' AND `item_id` = '%s' AND `type` = '%s'", $_POST['table'], $user['uid'], $_POST['item_id'], $_POST['type']);

        $delete = $framework->dbProcessor($sql, 0, 1);

      } else { 

        // Set the notification
        $notif = $_POST['type'] == 2 ? 1 : 2;
        $framework->dbProcessor(sprintf("INSERT INTO notification (`uid`, `by`, `object`, `type`) VALUES ('%s', '%s', '%s', '%s')", $_POST['item_id'], $user['uid'], $_POST['item_id'], $notif), 0, 1); 

        // Add the like
        $sql = sprintf("INSERT INTO %s (`type`, `user_id`, `item_id`) VALUES ('%s', '%s', '%s')", $_POST['table'], $_POST['type'], $user['uid'], $_POST['item_id']);

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
