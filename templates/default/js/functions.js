$(document).ready(function() {
	$(document).on('click touchend', '.track', function(e) {
	   var track = $(this).data('track-url');
	   var id = $(this).data('track-id');
	   var format = $(this).data('track-format');

	   $('#real-play'+id).html(1);

	   playSong(track, id, format);
	   e.preventDefault();
	});

	// Set the player volume
	if(localStorage.getItem("volume") === null) {
		localStorage.setItem("volume", player_volume);
	} else {
		player_volume = localStorage.getItem("volume");
	}

	$('.dolike').on('click', function() {
		let item_id = $(this).data('like-id');
		let type_ = $(this).data('type');
		let table = 'likes'; 

		$.ajax({
			type: 'POST',
			url: site_url+'/connection/doAction.php',
			data: {type: type_, item_id: item_id, table: table},
			dataType: 'JSON',
			success: function(data) {
				var up_likes = data.status == 1 ? Number($('#likes-count-'+item_id).text())+Number(1) : Number($('#likes-count-'+item_id).text())-Number(1);
				$('#likes-count-'+item_id).text(up_likes);
				$('.likes-counter-'+item_id).text(up_likes);
				$('#doLike_'+item_id+' i').toggleClass('text-danger'); 
				data.status == 1 ? $('#doLike_'+item_id+' i').addClass('ion-ios-heart') : $('#doLike_'+item_id+' i').removeClass('ion-ios-heart'); 
				data.status == 0 ? $('#doLike_'+item_id+' i').addClass('ion-ios-heart-empty') : $('#doLike_'+item_id+' i').removeClass('ion-ios-heart-empty'); 
				$('#doLike_'+item_id+' .text').text(data.status == 1 ? 'UNLIKE' : 'LIKE');
			}
		}); 
	});

	$('.dofollow').on('click', function() {
		let item_id = $(this).data('follow-id');
		let type_ = 3; 

		$.ajax({
			type: 'POST',
			url: site_url+'/connection/doAction.php',
			data: {type: type_, item_id: item_id},
			dataType: 'JSON',
			success: function(data) {
				var up_likes = data.status == 1 ? Number($('#followers-count-'+item_id).text())+Number(1) : Number($('#followers-count-'+item_id).text())-Number(1);
				$('#followers-count-'+item_id).text(up_likes);
				$('.followers-counter-'+item_id).text(up_likes);
				$('#doFollow_'+item_id+' i').toggleClass('low-blood'); 
				$('#doFollow_'+item_id).toggleClass('orange hover'); 
				data.status == 1 ? $('#doFollow_'+item_id+' i').addClass('ion-ios-person') : $('#doFollow_'+item_id+' i').removeClass('ion-ios-person'); 
				data.status == 0 ? $('#doFollow_'+item_id+' i').addClass('ion-ios-person-add') : $('#doFollow_'+item_id+' i').removeClass('ion-ios-person-add'); 
				$('#doFollow_'+item_id+' .text').text(data.status == 1 ? 'UNFOLLOW' : 'FOLLOW');
			}
		}); 
	});

	$('.dosubscribe').on('click', function() {
		let item_id = $(this).data('subscribe-id');
		let type_ = 4; 

		$.ajax({
			type: 'POST',
			url: site_url+'/connection/doAction.php',
			data: {type: type_, item_id: item_id},
			dataType: 'JSON',
			success: function(data) {
				var plus_it = data.status == 1 ? Number($('#subscribers-count-'+item_id).text())+Number(1) : Number($('#subscribers-count-'+item_id).text())-Number(1);
				$('#subscribers-count-'+item_id).text(plus_it);
				$('.subscribers-counter-'+item_id).text(plus_it);
				$('#doSubscribe_'+item_id).toggleClass('orange hover'); 
				data.status == 1 ? $('#doSubscribe_'+item_id+' i').addClass('ion-ios-person') : $('#doSubscribe_'+item_id+' i').removeClass('ion-ios-person'); 
				data.status == 0 ? $('#doSubscribe_'+item_id+' i').addClass('ion-ios-person-add') : $('#doSubscribe_'+item_id+' i').removeClass('ion-ios-person-add'); 
				$('#doSubscribe_'+item_id+' .text').text(data.status == 1 ? 'UNSUBSCRIBE' : 'SUBSCRIBE');
			}
		}); 
	});

	$('.doapprove').on('click', function() {
		let project = $(this).data('project-id');
		let user_id = $(this).data('user-id');
		var types = $(this).data('type');
		if (types == 1) {
			var type_ = 6;
		} else {
			var type_ = 5;
		}

		$.ajax({
			type: 'POST',
			url: site_url+'/connection/doAction.php',
			data: {type: type_, project: project, user_id: user_id},
			dataType: 'JSON',
			success: function(data) {
				var plus_it = data.status == 1 ? Number($('#collaborators-count-'+project).text())+Number(1) : Number($('#collaborators-count-'+project).text())-Number(1);
				$('#collaborators-count-'+project).text(plus_it);
				$('.collaborators-counter-'+project).text(plus_it);
				$('#doAction-'+types+'_'+user_id).toggleClass('orange hover'); 
				$('#special-request-'+user_id).slideToggle();
				if (types == 1 || types == 2) {
					$('#doAction-'+types+'_'+user_id+' .text').text(data.status == 1 ? 'CANCEL REQUEST' : 'REQUEST ENTRY');
				} else {
					$('#doAction-'+types+'_'+user_id+' .text').text(data.status == 1 ? 'REMOVE' : 'APPROVE');
				}
			}
		}); 
	}); 	

	$(document).on('keydown', 'textarea#write_msg', function(e) {
		if(e.keyCode==13) {
			sendMessage();
		}
	});	

	$(document).on('click', ".send-message", sendMessage);

	$(document).on('keyup', "#chat-search", searchMessages);

	$(document).on('click', "#notificationDropdown", function() { 
		$('.dropdown-notification-inner').html('<div class="text-center">'+spinner(2, 7, 2)+'</div>');
		checkNewNotifications(1);
	});

	$(document).on('click', "#messageDropdown", function() {
		$('.dropdown-message-inner').html('<div class="text-center">'+spinner(2, 7, 2)+'</div>');  
	});
});

// Check for new notifications, messages and incoming messages
console.log('timeOut interval and suspend: ', timeoutInt.interval, timeoutInt.suspend);
safeInterval(function(){
  // Refresh the notifications
   // loadNotifications(0);
   checkNewNotifications();
  // Refresh the chat 
  	if (page_name == 'messages' && $('#message-receiver').attr('value')) {
   		checkNewMessages();
  	}
}, timeoutInt.interval, timeoutInt.suspend); // timeoutInt.interval, timeoutInt.suspend default: 5000,300

function sendMessage() {
	// Store the message into var
	var message = $('textarea#write_msg').val();
	var rid = $('#message-receiver').attr('value');
	if (!rid) {
		var rid = $('#message-group').attr('value');
	}
	if(message) {
		// Remove chat errors if any
		$('.chat-error').remove();
		
		// Show the progress animation
		$('#loader').html(spinner(2, 7, 2, 1));
		
		// Reset the chat input area			
		$('textarea#write_msg').val('');
		 
		$.ajax({
			type: "POST",
			url: site_url+"/connection/send_message.php",
			data: 'message='+encodeURIComponent(message)+'&receiver='+rid,
			cache: false,
			success: function(html) {
				// Check if in the mean time any message was sent
				checkNewMessages();
				
				// Append the new chat to the div chat container
				$('.msg_history').append(html);
				$('#loader').hide(); 
				
				// Scroll at the bottom of the div (focus new content)
				$(".msg_history").scrollTop($(".msg_history")[0].scrollHeight);
			}
		});
	}
}

function loadMessages(user_id, username, chat_id, start) {
	if(!chat_id) {
		$('#top-header').show();
	}
	$('#loader, .more_loader').html(spinner(2, 7, 2, 1));

	$.ajax({
		type: "POST",
		url: site_url+"/connection/load_message.php",
		data: "user_id="+user_id+"&chat_id="+chat_id+"&start="+start, 
		cache: false,
		success: function(html) {
			// Remove the loader animation
			if(!chat_id) {
				$('.msg_history').empty();
				$('#top-header').hide();
			} else {
				$('.more-messages').remove();
			} 
			
			// Append the new message
			$('.msg_history').prepend(html);
			$('#loader').hide();
		
			if(username) { 
				$(".msg_history").scrollTop($(".msg_history")[0].scrollHeight);
			} 
		}
	});
}

function checkNewMessages(){
	var user_id = $('#message-receiver').attr('value');
	// Check whether user_id is defined or not (avoid making requests when out of the chat page)
	if(user_id) {
		$.ajax({
			type: "POST",
			url: site_url+"/connection/load_message.php",
			data: "user_id="+user_id+"&type=1",
			success: function(html) {
				if(html) {
					$('.msg_history').append(html);
					 
					$(".msg_history").scrollTop($(".msg_history")[0].scrollHeight);
				}
		   }
		});
	}
}

function checkNewNotifications(view) { 
	// Check whether user_id is defined or not (avoid making requests when out of the chat page) 
	if (view) { var status = '&view='+view;} else {var status = '';}
	$.ajax({
		type: "POST",
		url: site_url+"/connection/load_message.php",
		data: "type=2"+status,
		dataType: "JSON",
		success: function(response) {
			if(response) {
				$('.dropdown-notification-inner').html(response.notifications); 
				$('.dropdown-message-inner').html(response.messages); 
				$('#notification-counter').html(response.count_noti);
				$('#message-counter').html(response.count_msg);
			}
	   	},
	    error: function(xhr, status, error){
	      $('.dropdown-notification-inner').html(errorMessage(xhr, status, error));
	      $('.dropdown-message-inner').html(errorMessage(xhr, status, error));
	    }
	}); 
}

function searchMessages() {
	var q = $('#chat-search').val();
	$('#followers_chat').empty();
	
	// If the text input is 0, remove everything instantly by setting the MS to 1
	
	$('#search-loader').html(spinner(2, 7, 2, 1));
	var ms = 200;
	
	setTimeout(function() {
		if(q == $('#chat-search').val()) {
			
			$.ajax({
				type: "POST",
				url: site_url+"/connection/load_message.php",
				data: 'q='+q+'&search=1&list=1',  
				cache: false,
				success: function(html) {
					$('#search-loader').hide();
					$('#followers_chat').html(html);
				}
			});
		}
	}, ms);
}

// Send notifications to users
$('.send_msg_notn').on('click', function() {
  var receiver = $(this).data('receiver');
  toggleModal('sendMsgModal');
  $('#recipient_id').attr('value', receiver); 
  $('#recipient_name').html($(this).data('username'));
  $('#subject').val(''); 
  $('#message').val(''); 
  $('#response').html('');
});

$('#sendMultipleNotifications').on('click', function() {
  $('#subject').val(''); 
  $('#message').val(''); 
  $('#recipient_id').attr('value', ''); 
  $('#recipient_name').html('All Users');
  $('#response').html('');
});

function sendNotificationMsg(receiver) {
  var receiver = $('#recipient_id').attr('value');
  var subject = $('#subject').val(); 
  var message = $('#message').val(); 
  var type = $('input[name="message_type"]:checked').val(); 

  $('#response').html('<div class="text-center">'+spinner(0, 1, 0, 1)+'</div>');

  $.ajax({
    type: 'POST',
    url: site_url + '/connection/send_notification.php',
    data: {receiver: receiver, subject: subject, message: message, type: type},
    dataType: 'JSON',
    success: function (data) {
      $('#response').html(data.msg); 
    },
    error: function(xhr, status, error){
      $('#response').html(errorMessage(xhr, status, error));
    }
  });
}

function blockAction(id, type, feedback) {
	// type 0: View status
	// type 1: Block
	$('#block_').html(spinner(2, 7, 2, 1));
	$.ajax({
		type: "POST",
		url: site_url+"/connection/block.php",
		data: "id="+id+"&type="+type+"&feedback=2", 
		cache: false,
		success: function(html) {
			$('#block_').html(html);
		}
	});
}

function firstDelete(data) { 
	// Type 0: Notifications 
	// Type 1: Message
	var type = data.type;	console.log(data); 
	var query = {data:data};
	var action = data.action;
 
	$('#'+action+'_'+data.id).html(spinner(2, 7, 2, 1));

	if (deleteConfirm() == true) {
		$.ajax({
			type: "POST",
			url: site_url+"/connection/delete.php",
			data: query,
			dataType: "JSON", 
			success: function(html) {
				if(type == 0) {
					$('#set-message_'+data.id).html(html);
					$('#'+action+'_'+data.id).fadeOut(500, function() { $('#notification_'+data.id).remove(); }); 
				} else if(type == 1) { 
					if (html.status == 1) {
						$('#'+action+'_'+data.id).fadeOut(500, function() { $('#message_'+data.id).remove(); });
					} else {
						$.notify(html.status, 'warn');
					}
				}
			},
		    error: function(xhr, status, error){
		      $('#'+action+'_'+data.cid).html(errorMessage(xhr, status, error));
		    }

		});
	}
}

function projectFiles(type, project, id) {
  // type 1,4: Approve File
  // type 2,5: Hide File
  // type 3,6: Delete File
  if (type == 3 || type == 6) {
    $(".remove_file, .stem_"+id).slideToggle();
    $('.modal-dialog .close').click();
  }

  $.ajax({ 
    type: 'POST',
    url: site_url+'/connection/manage_project_files.php',
    data: {project: project, track_id: id, type: type},
    dataType: 'JSON',
    success: function(data) {
      $('#modal-content').html(data.main_content);
      $('#hidden-status').text(data.resp);
      $('.approve_hide').text(data.setting);
      if (type == 1 || type == 2 || type == 4 || type == 5) {
        $('.stem_extra_'+id+' i').toggleClass('pc-orange green ion-ios-checkmark-circle ion-ios-help-circle');
        $('.approve_hide').toggleClass('btn-success btn-warning');

        var action = type == 1 ? 2 : type == 2 ? 1 : type == 4 ? 5 : 4;
        $('.approve_hide').attr('onclick', 'projectFiles('+action+', '+project+', '+id+')'); 
      }   
    }
  });
}

function playlistManager(type, project, id) {
  // type 1,4: Create Playlist
  // type 2,5: Hide File
  // type 3,6: Delete File
  if (type == 3 || type == 6) {
    $(".remove_file, .stem_"+id).slideToggle();
    $('.modal-dialog .close').click();
  }

  $.ajax({ 
    type: 'POST',
    url: site_url+'/connection/manage_project_files.php',
    data: {project: project, track_id: id, type: type},
    dataType: 'JSON',
    success: function(data) {
      $('#modal-content').html(data.main_content);
      $('#hidden-status').text(data.resp);
      $('.approve_hide').text(data.setting);
      if (type == 1 || type == 2 || type == 4 || type == 5) {
        $('.stem_extra_'+id+' i').toggleClass('pc-orange green ion-ios-checkmark-circle ion-ios-help-circle');
        $('.approve_hide').toggleClass('btn-success btn-warning');

        var action = type == 1 ? 2 : type == 2 ? 1 : type == 4 ? 5 : 4;
        $('.approve_hide').attr('onclick', 'projectFiles('+action+', '+project+', '+id+')'); 
      }   
    }
  });
}

// Set an instrumental to public or project only
$(document).on('change', '#pub_instr', function(e) {
  var makePub = $('#pub_instr').prop('checked');
  var id = $(this).data('id');
  var project = $(this).data('project');
  var action = 0;
  if (makePub) {
    var action = 1;
  }

  $.ajax({
    type: 'POST',
    url: site_url+'/connection/manage_project_files.php',
    data: {project:project, track_id:id, action:action, type:7},
    dataType: 'JSON',
    success: function(data) {
      $('#public-status').html(data.resp);
    }
  });
});

// Function to load more results to the page 
function loadMore(this_) { 
	$("#load-more").html("<span class='load_anim'>"+spinner(4)+"</span>");
	var type = $(this_).data("last-type");
	var last_track = $(this_).data("last-track");
	var artist_id = $(this_).data("last-artist");
	var personal = $(this_).data("last-personal");
	var query = {type: type, last_track: last_track, artist: artist_id, personal:personal};

	$.ajax({
		type: 'POST',
		url: site_url+'/connection/moreResults.php',
		data: query, 
		dataType: 'JSON',
		success: function(data) {   
			$('#more-container').append(data.result);
			$('.more-container').append(data.result);
			$('#show-more-div').html(data.more);
			$('#more-container').slideDown("style");
			$("#load_anim").remove();
		}
	}); 
}

// Function to load more results to the page 
function loadMore_improved(this_, data) { 
	$(".load_more_div").html("<span class='load_anim'>"+spinner(4)+"</span>");
	var type = 6; 
	var query = {type: type, data: data};

	$.ajax({
		type: 'POST',
		url: site_url+'/connection/moreResults.php',
		data: query, 
		dataType: 'JSON',
		success: function(data) {
			$('.content-holder').append(data.result);
			$('.load-more-div').html(data.more);
			$("#load_anim").remove();
		}
	}); 
}

function options(type, options) {
	$.ajax({
		type: 'POST',
		url: site_url+'/connection/options.php',
		data: {type: type, data: options}, 
		dataType: 'JSON',
		success: function(data) {console.log(options.action);
			if (options.action == 'publish' && data.resp == 1) {
				$('#publish_btn_'+options.project).html(data.option);
				var style = data.status == 0 ? 'success' : 'warn';
				$.notify(data.msg, style);
			} else if (options.action == 'c_status' && data.resp == 1) { 
				var style = data.status == 1 ? 'success' : 'warn';
				$.notify(data.msg, style);
			}
		}
	});	
}  

function spinner(type, color, size, x) {
	switch(color) {
		case 1: var color = '-success';
			break;
		case 2: var color = '-warning';
			break;
		case 3: var color = '-danger';
			break;
		case 4: var color = '-info';
			break;
		case 5: var color = '-light';
			break;
		case 6: var color = '-dark';
			break;
		case 7: var color = '-primary';
			break;
		default: var color = '-secondary';
	}
	if (x) {
		if (type == 1) {
			var type = 'grow';
		} else {
			var type = 'border';
		}
		if (size == 1) {
			var size = ' spinner-'+type+'-sm';
		} else {
			var size = '';
		}
		return '<div class="spinner-'+type+' text'+color+size+'" role="status"> <span class="sr-only">Loading...</span> </div>';
	} else {
		return '<div id="loader-'+type+'"> <span></span> <span></span> <span></span> </div>';
	}

}

$('.wave_init').each(function(){
  	init = $(this);
    // Generate unique id
    var wid = '_' + Math.random().toString(36).substr(2, 9);
    var id = $(init).data('track-id');
    var audio = $(init).data('track-url');
    var format = $(init).data('track-format');
       
    //Set id to container
    // $(this).find(".wavesurfer-container").attr("id", id);

    // Initialize WaveSurfer
    wavesurfer = WaveSurfer.create({
        container: '#waveform' + id,
        waveColor: "#9a1d1d", cursorColor: "#fbfafa", progressColor: "#fbfafa",
        height: 67, barHeight: 1, barWidth: 2, barGap: 4
    });
    
    // Load audio file
    wavesurfer.on("finish", function() {
    	// wavesurfer.destroy();
    });
    wavesurfer.load(audio); 
    wavesurfer.setMute(true);
    wavesurfer.on("seek", function () {
        seeker(audio, id, format, wavesurfer.getCurrentTime());
    });
    wavesurfer.on("play", function () { 
        if (nowPlaying == id) {
			$("#sound-player").bind($.jPlayer.event.seeked, function(event) {
				wavesurfer.play(event.jPlayer.status.currentTime); 
			}); 
        } else {
        	wavesurfer.pause();
			$("#sound-player").unbind($.jPlayer.event.seeked); // Remove all play event listeners 
        } 
    });
}); 

$(".now-waving").on("click touchend", function(e) {
	$('#now-waving').html(1);
});

function seeker(song, id, format, time) {
	real_play = $("#real-play"+id).html(); 
	if (real_play == 1) playSong(song, id, format, time);
} 

function prevnext(type) {
  // Type 1: Previous Track
  // Type 2: Next Track
  // Type 3: Auto new tracks load when last track
  var currentId = $('.current-song').attr('id');
  
  var nextSong = $('.current-song').closest('#'+currentId).next().find('.track');
  var nextId = nextSong.attr('id');
  
  if(type == 3) {
    // If there's no next track available
    if(!nextId) {
      // If currently on the pages that have tracks with "Load More" buttons
      if(window.location.search.indexOf('page=listen') > -1 || window.location.search.indexOf('page=playlist') > -1 || window.location.search.indexOf('page=radio') > -1 || window.location.search.indexOf('page=explore') > -1 || (window.location.search.indexOf('page=profile') > -1 && window.location.search.indexOf('r=subscriptions') == -1) || (window.location.search.indexOf('a=profile') > -1 && window.location.search.indexOf('r=subscribers') == -1) || (window.location.search.indexOf('a=profile') > -1 && window.location.search.indexOf('r=playlists') == -1) || (window.location.search.indexOf('a=search') > -1 && window.location.search.indexOf('&filter=tracks') > -1) || window.location.href.indexOf(site_url+'/stream') > -1 || window.location.href.indexOf(site_url+'/explore') > -1 || (window.location.href.indexOf(site_url+'/profile') > -1 && ['about', 'subscriptions', 'subscribers', 'playlists'].indexOf(window.location.pathname.split("/").pop()) == -1) || (window.location.href.indexOf(site_url+'/search') > -1 && ['tracks'].indexOf(window.location.pathname.split("/").pop()) > -1 && ['filter'].indexOf(window.location.pathname.split("/").pop()) > -1)) {
        $('#load-more').click();
      }
    }
    return false;
  }
  var prevSong = $('.current-song').closest('#'+currentId).prev().find('.track');
  var prevId = prevSong.attr('id');
  
  if(prevId) {
    $('#prev-button').removeClass('prev-button-disabled');
    $('#prev-button').attr('onclick', 'prevnext(1)');
    if(type == 1) {
      document.getElementById(prevId).click();
      return;
    }
  } else {
    $('#prev-button').addClass('prev-button-disabled');
    $('#prev-button').removeAttr('onclick');
  }
  
  if(nextId) {
    $('#next-button').removeClass('next-button-disabled');
    $('#next-button').attr('onclick', 'prevnext(2)');
    if(type == 2) {
      document.getElementById(nextId).click();
      return;
    }
  } else {
    $('#next-button').addClass('next-button-disabled');
    $('#next-button').removeAttr('onclick');
  }
}

function repeatSong(type) {
	// Type 0: No repeat
	// Type 1: Repeat
	$('#refresh-icon').toggleClass('btn-active');
	if(type == 1) {
		$('#repeat-song').html('1'); 
    	$('#repeat-button').attr('onclick', 'repeatSong(0)');
	} else {
		$('#repeat-song').html('0');
    	$('#repeat-button').attr('onclick', 'repeatSong(1)');
	}
}

function shuffleTracks(type) {
	// Type 0: No repeat
	// Type 1: Repeat
	$('#shuffle-icon').toggleClass('btn-active');
	if(type == 1) {
		$('#shuffle-tracks').html('1'); 
    	$('#shuffle-button').attr('onclick', 'shuffleTracks(0)');
	} else {
		$('#shuffle-tracks').html('0'); 
    	$('#shuffle-button').attr('onclick', 'shuffleTracks(1)');
	}
}

function nextSong(id) {
	// If shuffle is turned on and the user is on a playlist page
	if($('#shuffle-tracks').html() == 1) { 
		// Select a random track from the page excluding the last played track
		var trackList = [];
		
		$('.song-container').not('.current-song').each( function ( index ) {
			trackList.push($(this).find('.track').attr('id'));
		});
		
		var nextSong = $('#'+trackList[Math.floor(Math.random()*trackList.length)]);
	} else {
		// Get the next song element
		var nextSong = $('.current-song').closest('#track'+id).next().find('.track');
	}
	
	// Get the next song element id
	var nextId = nextSong.attr('id'); 
		console.log(nextSong);
	
	// If one is available, move to the next track
	if(nextId) {
		document.getElementById(nextId).click();
	}
}
function playerVolume() {
	// Delay the function for a second to get the latest style value
	setTimeout(function() {
		// Get the style attribute value
		var new_volume = $(".jp-volume-bar-value").attr("style");
		
		// Strip off the width text
		var new_volume = new_volume.replace("width: ", "");
		
		if(new_volume != "100%;") {
			// Remove everything after the first two characters 00
			var new_volume = new_volume.substring(0, 2).replace(".", "").replace("%", "");
		}
		
		if(new_volume.length == 1) {
			var new_volume = "0.0"+new_volume;
		} else if(new_volume.length == 2) {
			var new_volume = "0."+new_volume;
		} else {
			var new_volume = 1;
		}
		
		// Save the new volume value
		localStorage.setItem("volume", new_volume);
	}, 1);	
}

// Allow volume bar dragging
$(document).on('mousedown', '.jp-volume-bar-value', function() {
	var parentOffset = $(this).offset(),
		width = $(this).width();
		$(window).mousemove(function(e) {
			var x = e.pageX - parentOffset.left,
			volume = x/width
			if (volume > 1) {
				$("#sound-player").jPlayer("volume", 1);
			} else if (volume <= 0) {
				$("#sound-player").jPlayer("mute");
			} else {
				$("#sound-player").jPlayer("volume", volume);
				$("#sound-player").jPlayer("unmute");
			}
			playerVolume();
		});
	return false;
});
$(document).on('mouseup', function() {
	$(window).unbind("mousemove");
});

// Validate a username
function validateInput(e,type, selector) {
	charStr = String.fromCharCode(e.keyCode); console.log(charStr);
  	if(/[a-zA-Z0-9-_]/.test(charStr) || e.keyCode == 13 || e.keyCode == 16 || e.keyCode == 8) {
  		var attribute = $(selector).attr('id');console.log(attribute);
 
      	var content = attribute; 

	    // Store the username into var
	    var data = $('input#'+content).val(); console.log(type+' '+data+' '+content);
      	(type == 1) ? $('#repusr').html(data) : '';

	    if(data) { 
	      	// Show the progress animation
	      	$('#'+content+'_check').html(spinner(1, 4, 1, 1));
	       
	      	$.ajax({
	        	type: "POST",
	        	url: site_url+"/connection/validation.php",
	        	data: 'data='+encodeURIComponent(data)+'&type='+type+'&attribute='+attribute,
	        	cache: false,
	        	success: function(html) {
	        		$('#'+content+'_check').html(html);
	        	}
	      	});
	    }
	}
}; 
