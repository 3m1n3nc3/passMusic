<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $user, $configuration, $framework, $databaseCL, $marxTime; 

	$PTMPL['page_title'] = $LANG['homepage'];	 
	
	$PTMPL['site_url'] = $SETT['url']; 

	$pid = isset($_GET['project']) ? $_GET['project'] : (isset($_GET['id']) ? $_GET['id'] : '');

    $databaseCL->user_id = $user['uid'];
    $project = $databaseCL->fetchProject($pid)[0];

    $databaseCL->project_title = $project['title'];
    $databaseCL->project_genre = $project['genre'];
    $databaseCL->project_tags = $project['tags'];
    $databaseCL->project_id = $project_id = $project['id'];
    $databaseCL->creator_id = $project['creator_id'];
    if (isset($_GET['creator'])) {
        $databaseCL->creator = $_GET['creator'];
        $author = $framework->userData($_GET['creator'], 1);
        $PTMPL['secondary_navigation'] = secondaryNavigation($_GET['creator']);
    }

    // Check if this user is colabing or is the creator 
    $allow_access = allowAccess($pid, 1); 

    $related = relatedItems(5, $project['id']);
    if ($related) {
        $PTMPL['project_related'] = relatedItems(5, $project['id']);
    } else {
        $PTMPL['no_related'] = notAvailable('No related projects');
    }

	$PTMPL['blur_filter'] = ' filter: blur(18px); -webkit-filter: blur(18px);';
    $PTMPL['extra_class'] = ' pc-full-width';
	$PTMPL['cover_photo'] = getImage($project['cover'], 1);
	$PTMPL['project_title'] = $project['cover'];
	$PTMPL['project_id'] = $project['id'];
	$PTMPL['project_details'] = $project['details'];
	$PTMPL['project_title'] = $project['title'];

    if ($project['status']) {
        $PTMPL['project_request_entry'] = clickApprove($project['id'], $user['uid'], 1);
        $PTMPL['project_status_1'] = ' checked';
    } else {
        $PTMPL['project_status_x'] = ' checked';
    }
    if ($project['published']) { 
        $PTMPL['project_publish'] = 0;
        $PTMPL['publish_btn'] = 'warning';
        $PTMPL['publish_btn_text'] = 'Unpublish';
    } else {
        $PTMPL['project_publish'] = 1;
        $PTMPL['publish_btn'] = 'success';
        $PTMPL['publish_btn_text'] = 'Publish';
    }

	$collabers = $databaseCL->fetch_projectCollaborators($project_id)[0];
	$PTMPL['count_collabers'] = $marxTime->numberFormater($collabers['counter'], 1);

	$PTMPL['project_collaborators'] = sidebar_projectCollaborators($project_id);

    // Set the content for the upload modal

    $PTMPL['upload_modal'] = modal('upload', '<div class="modal-container"></div>', '<span id="modal-title">Upload Audio</span>', 2);

	// Main Instrumentals
    $main_upload_btn = ' 
    <button 
      class="btn btn-info mx-auto mb-4 upload-modal-show" 
      id="main-instrumental-modal-show" 
      data-toggle="modal" 
      data-type="4"
      data-target="#uploadModal" 
      data-project-id="'.$project['id'].'">
      <i class="fa fa-upload"></i> '.$LANG['upload_main_track'].'
    </button>';

    $main_instrumental = '';
    $hidden = ' hidden';
    if ($project['instrumental']) {
        $main_instrumental = projectAudio($project);
        $hidden = '';
     } else {
        if ($allow_access) {
            $main_instrumental = notAvailable($LANG['missing_main_track']).$main_upload_btn;
            $hidden = '';
        }
     }
    $PTMPL['hidden'] = $hidden;
	$PTMPL['main_instrumental'] = $main_instrumental;

	// Other Instrumentals
    if ($allow_access) {
        $databaseCL->user_id = $user['uid'];
        $instrumentals = $databaseCL->fetchInstrumental($project_id);
        $list_instrumentals = '';
        if ($instrumentals) { 
            foreach ($instrumentals as $rows) { 
                $list_instrumentals .= projectAudio($rows, 1);
            } 
        }
        $PTMPL['list_instrumentals'] = $list_instrumentals;
    }

    // Project stems
	$get_stems = $databaseCL->fetchStems($project_id, 1);  
    $list_stems = '';
    if ($get_stems) {
        $get_stems = array_reverse($get_stems);
    	foreach ($get_stems as $rows) { 
    		$list_stems .= projectStems($rows);
    	}
    } else {
        $list_stems = notAvailable($LANG['no_stem_added']);
    }
    $PTMPL['list_stems'] = $list_stems; 

    // Project request
	$requests = $databaseCL->fetch_colabRequests($project_id);
    $list_requests = '';
    if ($requests) {  
    	foreach ($requests as $rows) { 
    		$list_requests .= specialRequestCard($rows);
    	}
    } else {
        $PTMPL['no_request'] = notAvailable($LANG['no_colab_request']);
    }
    $PTMPL['list_requests'] = $list_requests;

    // Hide or show the colab requests and project options tabs
    $restricted_tabs = $restricted_tabs_content = '';
    if ($user['uid'] == $project['creator_id']) {
        $restricted_tabs .= restrictedContent(1, 1);
        $restricted_tabs_content .= restrictedContent(1);
    }
    $PTMPL['restricted_tabs'] = $restricted_tabs;
    $PTMPL['restricted_tabs_content'] = $restricted_tabs_content;

    if ($allow_access) {
        $PTMPL['display_project_stems'] = restrictedContent(2);
        $PTMPL['display_manage_buttons'] = restrictedContent(3);
    }

	// Set the active landing
    if (isset($_GET['project']) || isset($_GET['id'])) {
        $theme = new themer('project/content');
    } else {
        $PTMPL['content_title'] = isset($author) ? '<div class="section-title">'.$author['fname'].' '.$author['lname'].'\'s Projects</div>' : '<div class="section-title">Public Projects</div>';    
        
        $projects = $databaseCL->fetchProject(0, 2);
        $show_projects = '';
        if ($projects) {
            foreach ($projects as $rows) {
                $show_projects .= projectsCard($rows);
            }
            $show_projects .= '<span style="display: none;" class="load-more-container"></span>';
            $PTMPL['artist_card'] = $show_projects;

            // Count all the associated records
            $databaseCL->counter = true;
            $count = $databaseCL->fetchProject(0, 2)[0]['counter'];
            $lcid = isset($author) ? ', last_cid: '.$author['uid'] : '';
            $PTMPL['load_more_div'] = $count > $configuration['page_limits'] ? '
            <div class="my-3 ml-4 load-more-div"><button onclick="loadMore_improved($(this), {type: 1, last: '.$rows['id'].$lcid.'})" class="show-more button-light" id="load-more">Load More</button>
            </div>' : '';
        } else {
            $PTMPL['artist_card'] = notAvailable($LANG['nor_artist_project']);
        }
        $theme = new themer('artists/view_artists');
    }
	
	return $theme->make();
} 
?>
