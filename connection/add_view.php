<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$status = '';
$type = $_POST['type'];
$id = $_POST['id']; 

if ($type == 1) { 
	$status = $databaseCL->addViews($id, $type = null);
}

$data = array('status' => $status);
echo json_encode($data, JSON_UNESCAPED_SLASHES); 
