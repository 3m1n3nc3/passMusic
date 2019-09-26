<?php 
require_once(__DIR__ .'/../includes/autoload.php');

$preview = $code = $msg = $status = $rslt = '';

if (isset($_GET['release'])) {

} else {
    $project = $_POST['project_id'];
    $action = $_GET['action'];
    $_type = $action == 'instrumental' ? '_INST' : ($action == 'stem' ? '_STEM' : '');
    $title = isset($_POST['title']) ? $framework->myTruncate($framework->db_prepare_input($_POST['title']), 25, " ", "") : '';
    $tags = isset($_POST['tags']) ? $_POST['tags'] : '';

    $databaseCL->user_id = $user['uid'];
    $prj = $databaseCL->fetchProject($project, 1)[0]; 
}

if (isset($_GET['action'])) {
    if ($action == 'stem_package') {
        $allowed = array('zip');
        $allowed_type = 'compressed zip';
        $path = 'files';
    } else {
        $allowed = array('mp3','wav');
        $allowed_type = 'MP3 and WAV audio';
        $path = 'audio';
    }
    $source = isset($_FILES['audioSource']) ? $_FILES['audioSource'] : null;
    $pid = $framework->db_prepare_input($_POST['project_id']);

    if($source && $source['error'] == 0){

        $extension = pathinfo($source['name'], PATHINFO_EXTENSION);

        if(!in_array(strtolower($extension), $allowed)){
            echo '{"status":"error", "msg":"You may only upload '.$allowed_type.' files", "rslt":"empty"}';
            exit;
        } 
        $_title = $framework->generateToken(7, 3).$_type;
        $new_audio = $_title.'.'.$extension;
        $cd = getcwd(); 
        $target_path = $SETT['working_dir'].'/uploads/'.$path.'/';

        // Now upload the file to the server and update the database
        if(move_uploaded_file($source['tmp_name'], $target_path.$new_audio)){ 

            if ($action == 'instrumental') {
                $sql = sprintf("INSERT INTO instrumentals (`user`, `project`, `title`, `tags`, `file`) VALUES ('%s', '%s', '%s', '%s', '%s')", $user['uid'], $project, $title, $tags, $new_audio);
                $results = $framework->dbProcessor($sql, 0, 1);
            } elseif ($action == 'stem') {
                $sql = sprintf("INSERT INTO stems (`user`, `project`, `title`, `tag`, `file`) VALUES ('%s', '%s', '%s', '%s', '%s')", $user['uid'], $project, $_title, $tags, $new_audio);
                $results = $framework->dbProcessor($sql, 0, 1);
            } elseif ($action == 'main_instrumental') {
                $sql = sprintf("UPDATE projects SET `instrumental` = '%s' WHERE `id` = '%s'", $new_audio, $project);
                
                 // Delete the current main instrumental if updating 
                $del = deleteFile($prj['instrumental'], null, 1);
                $results = $framework->dbProcessor($sql, 0, 1); 
            } elseif ($action == 'stem_package') {
                $sql = sprintf("UPDATE projects SET `datafile` = '%s' WHERE `id` = '%s'", $new_audio, $project);
                
                 // Delete the current stem package if updating 
                $del = deleteFile($prj['datafile'], null, 1);
                $results = $framework->dbProcessor($sql, 0, 1); 
            }
            if ($results == 1) {
                echo '{"status":"success", "msg":"Upload Complete", "rslt":"'.$new_audio.'"}';
            } else {
                echo '{"status":"error", "msg":"DB Error: '.$results.'", "rslt":"empty"}';
            }
            exit;
        } 

        $data = array('msg' => '$msg', 'preview' => $_POST);
        echo json_encode($data, JSON_UNESCAPED_SLASHES); 
    }
} elseif (isset($_GET['release'])) {
    // Upload audio or artwork for new releases
    if(isset($_POST) && $_SERVER['REQUEST_METHOD'] == "POST") {

        $release_id = $framework->db_prepare_input($_GET['release']);

        // Upload new release audio
        if ($_GET['rel'] == 'audio') {
            $file_name      = strip_tags($_FILES['upload_file']['name']);
            $file_id        = strip_tags($_POST['upload_file_ids']);
            $file_size      = $_FILES['upload_file']['size'];
            $files_path     = $SETT['working_dir'].'/uploads/audio/';
            $extension      = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_name       = $framework->generateToken(5, 3).'.'.$extension;
            $title          = pathinfo($file_name, PATHINFO_FILENAME);
            $file_location  = $files_path . $new_name;
            $temp_image     = $_FILES['upload_file']['tmp_name']; 
            
            $marxTime->part = 1;
            $title = $marxTime->reconstructString($title);

            if (move_uploaded_file(strip_tags($temp_image), $file_location)){
                $sql = sprintf("INSERT INTO new_release_tracks (`release_id`, `title`, `audio`) VALUES ('%s', '%s', '%s')", $release_id, $title, $new_name);
                $results = $framework->dbProcessor($sql, 0, 1); 
                if ($results == 1) {
                    echo $file_id;
                } else {
                    echo 'system_error';
                }
            } else{
                echo 'system_error';
            }   

            // Upload new release artwork
        } elseif ($_GET['rel'] ==  'artwork') {
            $file_name      = strip_tags($_FILES['upload_image']['name']); 
            $file_size      = $_FILES['upload_image']['size'];
            $files_path     = $SETT['working_dir'].'/uploads/photos/';
            $temp_image     = $_FILES['upload_image']['tmp_name'];
            $image_size     = getimagesize($_FILES['upload_image']['tmp_name']);

            $extension      = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_image      = mt_rand().'_'.mt_rand().'_'.mt_rand().'_n.'.$extension; 
            $file_location  = $files_path . $new_image;

            if ($image_size[0] === 1000 & $image_size[1] === 1000) {
                $msg = messageNotice('Image resolution must be 3000 X 3000', 3); 
                $status = 'error';
                $code = 0;
            } else {
                if (move_uploaded_file(strip_tags($temp_image), $file_location)) {
                    $sql = sprintf("UPDATE new_release SET `art` = '%s' WHERE `release_id` = '%s'", $new_image, $release_id);
                    $results = $databaseCL->dbProcessor($sql, 0, 1);
                    if ($results == 1) {
                        $msg = messageNotice('Your Artwork was uploaded successfully', 1);
                        $status = 'success';
                        $code = 1;
                    } else {
                        $msg = $results;
                        $status = 'error';
                        $code = 0;
                    }
                }
            } 
            $data = array('status_code' => $code, 'status' => $status, 'msg' => $msg);  
            echo json_encode($data, JSON_UNESCAPED_SLASHES);  
        }
    }
} else { 
    echo '{"status":"error", "msg":"You may not upload an empty file", "rslt":"empty"}';
    exit;
}
