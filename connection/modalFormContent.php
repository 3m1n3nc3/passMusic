<?php 
require_once(__DIR__ .'/../includes/autoload.php');

$post_type = $_POST['type'];

$tag_list = [];
$tags = $databaseCL->fetchGenre();
if ($tags) {
  foreach ($tags as $value) {
    $tag_list[] = '\''.$value['name'].'\'';
  }
}
$tag_list = implode(', ', $tag_list);

$tag_sel = $databaseCL->fetchTags();
$tag_opt = '';
if ($tag_sel) {
  foreach ($tag_sel as $row) {
    $tag_opt .= '<option value="'.$row['name'].'">'.$row['title'].'</option>';
  }
}

$content = '';
$project_id = $_POST['project_id'];
$track_id = isset($_POST['track_id']) ? $_POST['track_id'] : null;

// Fetch the data from the project
$databaseCL->user_id = $user['uid'];
$project = $databaseCL->fetchProject($project_id)[0];

$title_field = '';
if ($post_type == 1 || $post_type == 2) {
  if ($_POST['type'] == 1) {
    $processor = $SETT['url'].'/connection/uploader.php?action=instrumental';
    $t_tag = 'Tags';
    $tag_hint = 'Maximum of 5';
    $tag_field = '
      <input type="text" class="form-control" name="tags" id="set-tags" placeholder="'.$t_tag.'">';
    $title_field = '
      <div class="form-group">
        <label for="title">Title:</label>
        <input type="text" class="form-control" name="title" placeholder="Title" id="title">
      </div>';
  } else { 
    $processor = $SETT['url'].'/connection/uploader.php?action=stem';
    $t_tag = 'Tag';
    $tag_hint = 'Tag this stem';
    $tag_field = ' 
      <select class="form-control" id="select-tag" name="tags">
        '.$tag_opt.'
      </select>';
  }

  $content = '
  <script>
    $("#set-tags").tagEditor({ 
        delimiter: ", ", /* space and comma */
        placeholder: "Enter tags...",
        forceLowercase: true,
        maxTags: 15,
        autocomplete: {
            delay: 0, // show suggestions immediately
            position: { collision: "flip" }, // automatic menu position up/down
            source: ['.$tag_list.']
        }      
    });

    $(function(){
      $( "#title" ).EnsureMaxLength({
        limit: 25
      });
    });
  </script>

  <form id="project-uploader" data-type="'.$_POST['type'].'" enctype="multipart/form-data" method="post" action="'.$processor.'">
      <div id="upload_message"></div>
      <label class="p-4 btn btn-primary btn-block font-weight-bold" for="audioSource"><i class="fa fa-music"></i> Choose Audio File...</label><br>
      <input style="display: none;" type="file" name="audio" id="audioSource" onchange="fileSelected();" accept=".wav, .mp3" />
      <input type="hidden" id="project_id" value="'.$project_id.'">
      <input type="hidden" id="action_url" value="'.$processor.'">
      '.$title_field.'
      <div class="form-group">
        <label for="set-tags">'.$t_tag.':</label> <small class="text-danger">'.$tag_hint.'</small>
        '.$tag_field.'
      </div>
      <div id="info-div" class="my-1">
        <div id="fileName"></div> 
        <div id="fileSize"></div> 
        <div id="fileType"></div>
        <input id="upload_btn" class="btn btn-info" type="button" value="Upload" />
      </div>
      <div id="progressNumber"></div>
      <div id="upload_status"></div>
  </form>';
} elseif ($post_type == 3 || $post_type == 5) {
  // Approve, remove or hide stem or instrumentals
  $stem = $databaseCL->fetchStems($track_id, 2)[0];
  $instr = $databaseCL->fetchInstrumental($track_id, 1)[0];
  $user_id = $post_type == 3 ? $stem['user'] : $instr['user'];
  $user_ = $framework->userData($user_id, 1);

  $button_remove = $button_approve = $check_box = '';
  if ($post_type == 3) {
    $del_type = 3;
    $heading ='<h2>Manage Stem File</h2>';
    if ($stem['status'] == 1) {
      $set_type = 2;
      $set_btn = 'warning';
      $set_name = 'Hide';
    } else {
      $set_type = 1;
      $set_btn = 'success';
      $set_name = 'Approve';    
    }
  } else {
    // Manage instrumentals
    $del_type = 6;
    $status = $instr['hidden'] == 0 ? 'Approved' : 'Hidden';
    $public = $instr['public'] == 1 ? 'Public' : 'Project Only';
    $checked = $instr['public'] == 1 ? 'checked="checked"' : '';
 
    $l_tags = showTags($instr['tags']); 

    $heading ='
    <div class="panel panel-default">
      <div class="panel-heading">Manage Instrumental File</div> 
      <div class="panel-body"> 
        <strong>'.$instr['title'].'</strong> 
        <div class="row header-summary mt-3">
            <div class="col-md-4">
              <h6><i class="ion-ios-person"></i> '.$user_['fname'].' '.$user_['lname'].'</h6>
            </div>
            <div class="col-md-4">
              <h6><i class="ion-ios-switch"></i> <span id="hidden-status">'. $status.'</span></h6>
            </div> 
            <div class="col-md-4">
              <h6><i class="ion-ios-globe"></i> <span id="public-status">'. $public.'</span></h6>
            </div>  
            <div class="col-md-12">
              <h6>'. $l_tags .'</h6>
            </div>  
        </div>
      </div>     
    </div>';
    $check_box = '
    <div class="font-weight-bold d-flex pc-font-2" title="Make this instrumental public">
      <input type="checkbox" name="pub_instr" id="pub_instr" data-project="'.$project_id.'" data-id="'.$track_id.'"'.$checked.'><div class="font-weight-bold mx-2 text-info"> Make Public</div>
    </div><div>'.$LANG['public_notice'].'</div>';
    if ($instr['hidden'] == 0) {
      $set_type = 4;
      $set_btn = 'warning';
      $set_name = 'Hide';
    } else {
      $set_type = 5;
      $set_btn = 'success';
      $set_name = 'Approve'; 
    }
  }
  $button_approve = '<a href="#" class="btn btn-'.$set_btn.' pc-font-md approve_hide" onclick="projectFiles('.$set_type.', '.$project_id.', '.$track_id.')">'.$set_name.'</a>';

  $button_remove = '<a href="#" class="btn btn-danger pc-font-md remove_file" onclick="projectFiles('.$del_type.', '.$project_id.', '.$track_id.')">Remove</a> ';

  $check_box = $user['uid'] == $instr['user'] ? $check_box : '';
  $button_approve = $user['uid'] == $project['creator_id'] ? $button_approve : '';
  $hide_notice = $button_approve ? $LANG['hide_notice'] : '';
  if ($post_type == 5) {
    $button_remove = $user['uid'] == $instr['user'] ? $button_remove : '';
  } else {
    $button_remove = $button_remove;
  }

  $content .= '
  <div class="text-center text-info m-3">'.$heading.'</div>
  '.$check_box.'
  '.$hide_notice.'
  <div class="btn-group btn-group-justified">
    '.$button_approve.'
    '.$button_remove.'
  </div>';
} elseif ($post_type == 4) { 
  // Upload the main instrumental
  $processor = $SETT['url'].'/connection/uploader.php?action=main_instrumental';
  $content = ' 
  <form id="project-uploader" data-type="'.$_POST['type'].'" enctype="multipart/form-data" method="post" action="'.$processor.'">
    <div id="upload_message"></div>
    <label class="p-4 btn btn-primary btn-block font-weight-bold" for="audioSource"><i class="fa fa-music"></i> Choose Audio File...</label><br>
    <input style="display: none;" type="file" name="audio" id="audioSource" onchange="fileSelected();" accept=".wav, .mp3" />
    <input type="hidden" id="project_id" value="'.$project_id.'">
    <input type="hidden" id="action_url" value="'.$processor.'">
    <div id="info-div" class="my-1">
      <div id="fileName"></div>
      <div id="fileSize"></div>
      <div id="fileType"></div>
      <input id="upload_btn" class="btn btn-info" type="button" value="Upload" />
    </div>
    <div id="progressNumber"></div>
    <div id="upload_status"></div>
  </form>';
} elseif ($post_type == 6) {
  // Manage the datafile zip package
  $user_ = $framework->userData($project['creator_id'], 1);
  $status = $project['datafile'] ? $project['datafile'] : 'Not Uploaded';
  if (fileInfo($project['datafile'])) {
    $size = $marxTime->swissConverter(fileInfo($project['datafile'])['filesize'], 1);
    $download_name = ' download="'.strtoupper($project['title'].'-STEM-FILE-PACK_'.$project['datafile']).'"';
    $disable = '';
  } else {
    $download_name = '';
    $size = '0.00 MB';
    $disable = ' disabled';
  }

  $button_update = '
  <a href="#" 
    class="btn btn-warning pc-font-md show-upload-container"
    data-referrer="'.$post_type.'"
    onclick="showUploadContainer(7, '.$project['id'].')">
    <i class="ion-ios-cloud-upload"></i> Update
  </a>';
  $button_update = $user['uid'] == $project['creator_id'] ? $button_update : '';

  $button_download = '<a href="'.getFiles($project['datafile']).'" class="btn btn-info pc-font-md"'.$download_name.$disable.'><i class="ion-ios-cloud-download"></i> Download</a> ';

  $heading ='
  <div class="panel panel-default">
    <div class="panel-heading">Stem Package</div>
    <div class="panel-body">
      <strong>'.$project['title'].'</strong>
      <div class="row header-summary mt-3">
        <div class="col-md-4">
          <h6><i class="ion-ios-person"></i> '.$user_['fname'].' '.$user_['lname'].'</h6>
        </div>
        <div class="col-md-4">
          <h6><i class="ion-ios-archive"></i> <span id="hidden-status">'. $status.'</span></h6>
        </div>
        <div class="col-md-4">
          <h6><i class="ion-ios-cloud-download"></i> <span id="public-status">'.$size.'</span></h6>
        </div>
      </div>
    </div>
  </div>';

  $content .= '
  <div class="text-center text-info m-3">'.$heading.'</div> 
  <div class="modal-inner-container"></div>
  <div class="btn-group btn-group-justified">
    '.$button_update.'
    '.$button_download.'
  </div>';  
} elseif ($post_type == 7) {
  // Upload the stem package
  $processor = $SETT['url'].'/connection/uploader.php?action=stem_package';
  $content = ' 
  <form id="project-uploader" data-type="'.$_POST['type'].'" enctype="multipart/form-data" method="post" action="'.$processor.'">
    <div id="upload_message"></div>
    <label class="p-4 btn btn-primary btn-block font-weight-bold" for="audioSource"><i class="fa fa-music"></i> Choose Zip File...</label><br>
    <input style="display: none;" type="file" name="audio" id="audioSource" onchange="fileSelected();" accept=".zip" />
    <input type="hidden" id="project_id" value="'.$project_id.'">
    <input type="hidden" id="action_url" value="'.$processor.'">
    <span id="info-div" class="my-1">
      <div id="fileName"></div>
      <div id="fileSize"></div>
      <div id="fileType"></div>
      <input id="upload_btn" class="btn btn-info" type="button" value="Upload" />
    </span>
    <input class="btn btn-warning" type="button" value="Back" onclick="showUploadContainer(6, '.$project_id.')"/>
    <div id="progressNumber"></div>
    <div id="upload_status"></div>
  </form>';
}

$data = array('main_content' => $content, 'title' => '$loader', 'navigation' => '$tag_list');

echo json_encode($data, JSON_UNESCAPED_SLASHES); 
