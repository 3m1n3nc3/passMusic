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

	$('#dolike').on('click', function() {
		let item_id = $(this).data('like-id');
		let type_ = $(this).data('type');
		let table = 'likes'; 

		$.ajax({
			type: 'POST',
			url: site_url+'/connection/doAction.php',
			data: {type: type_, item_id: item_id, table: table},
			dataType: 'JSON',
			success: function(data) { 
				console.log(data.msg);
				$('#dolike i').toggleClass('text-danger');
			}
		}); 
	});
});

$('#wave_init').each(function(){
  wavesurfer_in = $('#wave_init');
    // Generate unique id
    var wid = '_' + Math.random().toString(36).substr(2, 9);
    var id = $(wavesurfer_in).data('track-id');
    var audio = $(wavesurfer_in).data('track-url');
    var format = $(wavesurfer_in).data('track-format');
       
    // Initialize WaveSurfer
    var wavesurfer = WaveSurfer.create({
        container: '#waveform' + id,
        height: 67, barHeight: 1, waveColor: "#9a1d1d",  barGap: 4,
        barWidth: 2, progressColor: "#fbfafa"
    });
    
    // Load audio file
    wavesurfer.load(audio); 
    wavesurfer.setMute(true);
    wavesurfer.on("seek", function () {
        seeker(audio, id, format, wavesurfer.getCurrentTime());
    });
});

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
      if(window.location.search.indexOf('page=radio') > -1 || window.location.search.indexOf('page=explore') > -1 || (window.location.search.indexOf('page=profile') > -1 && window.location.search.indexOf('r=subscriptions') == -1) || (window.location.search.indexOf('a=profile') > -1 && window.location.search.indexOf('r=subscribers') == -1) || (window.location.search.indexOf('a=profile') > -1 && window.location.search.indexOf('r=playlists') == -1) || (window.location.search.indexOf('a=search') > -1 && window.location.search.indexOf('&filter=tracks') > -1) || window.location.href.indexOf(site_url+'/stream') > -1 || window.location.href.indexOf(site_url+'/explore') > -1 || (window.location.href.indexOf(site_url+'/profile') > -1 && ['about', 'subscriptions', 'subscribers', 'playlists'].indexOf(window.location.pathname.split("/").pop()) == -1) || (window.location.href.indexOf(site_url+'/search') > -1 && ['tracks'].indexOf(window.location.pathname.split("/").pop()) > -1 && ['filter'].indexOf(window.location.pathname.split("/").pop()) > -1)) {
        $('#infinite-load').click();
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
		console.log('shuffle off working '+nextId);
		console.log(nextSong);
	
	// If one is available, move to the next track
	if(nextId) {
		document.getElementById(nextId).click();
	}
}
function seeker(song, id, format, time) {
	let real_play = $("#real-play"+id).html();
	console.log(real_play);
	if (real_play == 1) playSong(song, id, format, time);
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
