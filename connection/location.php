<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$id = $_POST['type'] == 2 ? $_POST['country_id'] : $_POST['state_id'];

$data = defineLocale($_POST['type'], $id);
echo $data;
