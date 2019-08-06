<?php 
require_once(__DIR__ .'/../includes/autoload.php');
  $user['id'] = 1; 

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
