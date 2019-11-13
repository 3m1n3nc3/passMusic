<?php 
require_once(__DIR__ .'/../includes/autoload.php');

$preview = $code = $msg = $status = $rslt = $path = 'scope_'; $errors = [];

// File arguments
$errors = array();
$file_name = $_FILES['upload']['name'];
$file_size = $_FILES['upload']['size'];
$file_tmp = $_FILES['upload']['tmp_name'];
$file_type = $_FILES['upload']['type'];
$extension = pathinfo($file_name , PATHINFO_EXTENSION);

$all_extensions = array("jpeg","jpg","png");

$new_image = mt_rand().'_'.mt_rand().'_n.'.$extension; 
if (isset($user)) {
    $path = $user['username'].'_'.$user['uid'];
} elseif (isset($admin)) {
    $admin_user = $framework->userData($admin['admin_user'], 1);
    $path = $admin_user['username'].'_'.$admin_user['uid'];
}

if(in_array($extension, $all_extensions) === false){
    $error = 'File type not allowed, use a JPEG, JPG or PNG file';
} else {         
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'ckeditor') { 
            $directory = $SETT['working_dir'].'/uploads/photos/'.$path.'/';
            if (!file_exists($directory)) {
                mkdir($directory);
            }
        }
    } else {

    }
    if (isset($directory) && file_exists($directory)) {
        // Create a new ImageResize object
        $image = new \Gumlet\ImageResize($file_tmp);
        $image->resizeToHeight(700);  
        $image->save($directory.$new_image);
    } else {
        $error = 'The upload directory defined in the server does not exist';
    }
}

// Check for error and return a response to the client
if (!isset($error)) {
    $up_url = str_ireplace('admin.', '', $SETT['url']);
    echo '{"uploaded": true, "url": "'.$up_url.'/uploads/photos/'.$path.'/'.$new_image.'" }';
} else { 
    echo '{"uploaded":false, "error":{ "message": "'.$error.'" } }';
    exit;
}  

