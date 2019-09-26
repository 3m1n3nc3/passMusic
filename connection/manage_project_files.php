<?php 
require_once(__DIR__ .'/../includes/autoload.php');

$post_type = $_POST['type'];
$post_action = isset($_POST['action']) ? $_POST['action'] : null;
$get_stem = $databaseCL->fetchStems($_POST['track_id'], 2)[0];
$get_instr = $databaseCL->fetchInstrumental($_POST['track_id'], 1)[0];

$response = ''; 
$do = $delete = 0; 
$setting = 'Default';
if ($post_type == 1 || $post_type == 2) {
	$new_sts = $post_type == 1 ? 1 : 0;
	$sql = sprintf("UPDATE stems SET `status` = '%s' WHERE `id` = '%s' AND `project` = '%s'", $new_sts, $_POST['track_id'], $_POST['project']); 
    $do = $framework->dbProcessor($sql, 0, 1);
	$setting = $new_sts == 1 ? 'Hide' : 'Approve';
} elseif ($post_type == 3) {
	$sql = sprintf("DELETE FROM stems WHERE `id` = '%s' AND `project` = '%s'", $_POST['track_id'], $_POST['project']);
	$del = deleteFile($get_stem['file']);
    $do = $del == 1 ? $framework->dbProcessor($sql, 0, 1) : 0;
} elseif ($post_type == 4 || $post_type == 5) {
	$new_sts = $post_type == 4 ? 1 : 0;
	$sql = sprintf("UPDATE instrumentals SET `hidden` = '%s' WHERE `id` = '%s' AND `project` = '%s'", $new_sts, $_POST['track_id'], $_POST['project']); 
    $do = $framework->dbProcessor($sql, 0, 1);
	$response = $new_sts == 0 ? 'Approved' : 'Hidden';
	$setting = $new_sts == 0 ? 'Hide' : 'Approve';
} elseif ($post_type == 6) {
	$sql = sprintf("DELETE FROM instrumentals WHERE `id` = '%s' AND `project` = '%s'", $_POST['track_id'], $_POST['project']); 
	$del = deleteFile($get_instr['file']);
    $do = $del == 1 ? $framework->dbProcessor($sql, 0, 1) : 0;
} elseif ($post_type == 7) { 
	$sql = sprintf("UPDATE instrumentals SET `public` = '%s' WHERE `id` = '%s' AND `project` = '%s'", $post_action, $_POST['track_id'], $_POST['project']); 
	$do = $framework->dbProcessor($sql, 0, 1);
	$response = $post_action == 1 ? 'Public' : 'Project Only';
}
if ($do == 1) {
	$status = $msg = 1; 
} else {
	$status = 0; 
	$msg = $do; 
}

$data = array('status' => $status, 'msg' => $msg, 'setting' => $setting, 'resp' => $response);

echo json_encode($data, JSON_UNESCAPED_SLASHES); 
