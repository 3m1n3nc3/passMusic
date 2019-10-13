// Tooltips

$(function () {
  $('[data-toggle="tooltip"]').tooltip();

  $('.profile_tooltip').tooltip({
    delay: 500, 
    trigger: "focus hover",
    placement: "top",
    title: profileCard,
    html: true
  });
})

function toggleModal(modal_name) { 
  // $('#'+modal_name).modal('dispose');
  $('#'+modal_name).modal('toggle');
}

function checkbox(boxname) {
  $('input[name="'+boxname+'"]').val(function(){
        if(this.checked == true) {
            p = '1'; }
        else {
            p = '0'; }        
    });
    return p;
}

function deleteItem(options) {
  var quest = options.conf_ ? options.conf_ : 'Are you sure you want to delete?';
  var conf = confirm(quest);
  if (conf == true) {
    $.ajax({
      type: 'POST',
      url: site_url+'/connection/delete.php',
      data: {data: options},
      dataType: 'JSON',
      success: function(data) {
        if (data.status == 1) {
          if (options.type == 1) {
            $('#track'+options.track).fadeOut('slow');
            $.notify(data.msg, 'success');
          } else if (options.type == 2) {
            $.notify(data.resp, 'success');
            $('.tempnotice').html(data.msg);
            $('#artist_'+options.id).fadeOut('slow');
          }
        } else {
          $.notify(data.msg, 'error');
          $('.tempnotice').html(data.msg);
        }
      }
    });
  } else {
    return false;
  }
}

function profileCard() {
  // ID: Unique user ID
  var type = $(this).data('type');
  var options = $(this).data('options');

  $.ajax({
    type: "POST",
    url: site_url+"/connection/load_hovercard.php",
    data: {type: type, data: options}, 
    async: false,
    dataType: 'JSON',
    success: function(response) {     
      tooltipText = response.html;
    },
    error: function(xhr, status, error){
      tooltipText = errorMessage(xhr, status, error);
    }
  });
  return tooltipText;
} 

$('.upload-modal-show').on('click', function() {
  var project_id = $(this).data('project-id');
  var upload_type = $(this).data('type');
  $('.modal-container').html('<div class="text-center justify-content-center container">'+spinner(3)+'</div>');

  $.ajax({
    type: 'POST',
    url: site_url+'/connection/modalFormContent.php',
    data: {project_id:project_id, type: upload_type},
    dataType: 'JSON',
    success: function(data) {
      $('.modal-container').html(data.main_content);
      console.log(project_id);

    }
  });
});

$('.manage-modal-show').on('click', function() {
  var project_id = $(this).data('project-id');
  var track_id = $(this).data('track-id'); 
  var c_type = $(this).data('type');
  $('#modal-title').html('Manage Content');
  $('.modal-container').html('<div class="text-center justify-content-center container">'+spinner(3)+'</div>');

  $.ajax({
    type: 'POST',
    url: site_url+'/connection/modalFormContent.php',
    data: {project_id:project_id, track_id: track_id, type: c_type},
    dataType: 'JSON',
    success: function(data) {
      $('.modal-container').html(data.main_content);
      console.log(project_id);

    }
  });
});

function playlistAction(type, data) {
  if (data.action == 'create') {  
    var public = checkbox('make_public'); 
    var title = $('input[name="title"]').val();
    var data = {action: data.action, title: title, public: public};
  } else {
    var playlist = $('select[name="playlist"] option:selected').val();
    var data = {action: data.action, track: data.track, playlist: playlist};
  }
  $.ajax({
    type: 'POST',
    url: site_url+'/connection/options.php',
    data: {type: type, data: data},
    dataType: 'JSON',
    success: function(data) {
      $('#save_message').html(data.option);
    },
    error: function(xhr, status, error){
      $('#save_message').html(errorMessage(xhr, status, error));
    }
  });
}

function playlist_modal(type, data) {  
  $('.modal-container').html('<div class="text-center justify-content-center container">'+spinner(3)+'</div>');

  $.ajax({
    type: 'POST',
    url: site_url+'/connection/options.php',
    data: {type: type, data: data},
    dataType: 'JSON',
    success: function(data) { 
      $('.modal-container').html(data.option); 
    }
  });
};

function showUploadContainer(type, project, track) {  
  var ref = $('.show-upload-container').data('referrer');
  $.ajax({
    type: 'POST',
    url: site_url+'/connection/modalFormContent.php',
    data: {project_id:project, track_id: track, type: type, ref:ref},
    dataType: 'JSON',
    success: function(data) {
      $('.modal-container').html(data.main_content); 
    }
  });
}

// Reload the page
function reloadPage(cache) {
  // cache: true/false
  window.location.reload(cache);
}

// Viewport Heights
$(window).on("resize load", function(){
  
  var totalHeight = $(window).height();

  var headerHeight = $('.header').outerHeight();
  var footerHeight = $('.current-track').outerHeight();
  var playlistHeight = $('.playlist').outerHeight();
  var nowPlaying = $('.playing').outerHeight();

  var navHeight = totalHeight - (headerHeight + footerHeight + playlistHeight + nowPlaying);
  var artistHeight = totalHeight - (headerHeight + footerHeight);

  console.log(totalHeight);
  
  $(".navigation").css("height" , navHeight);
  $(".artist").css("height" , artistHeight);
  $(".social").css("height" , artistHeight);
  
});

// Collapse Toggles
$(".navigation__list__header").on( "click" , function() {
  $(this).toggleClass( "active" );
});

$(".tab-activator li").on( "click" , function() {
  $(this).toggleClass( "active" ); 
  $(this).siblings().removeClass("active");
});

// Media Queries
$(window).on("resize load", function(){
  if ($(window).width() <= 768){  
    
    $(".collapse").removeClass("in");
    
    $(".navigation").css("height" , "auto");
    
    $(".artist").css("height" , "auto");
    
  } 
});

$(window).on("resize load", function(){
  if ($(window).width() > 768){ 
    
    $(".collapse").addClass("in");
    
  } 
});

// ClipboardJS click to copy
var clipboard = new ClipboardJS('#copyable');
clipboard.on('success', function(e) { 
  $.notify("Copied", "info");
});
clipboard.on('error', function(e) {
    console.log(e);
});

// Character count limit
! function(n, t) {
    var s = {
        limit: 100,
        cssClass: "",
        separator: "/",
        placement: null
    };

    function i(t, i) {
        this.element = n(t), this.options = n.extend({}, s, i), this.$counter = n("<small />").addClass(this.options.cssClass), this.init()
    }
    i.prototype.init = function() {
        var t = this;
        this.showCount(), this.element.on("keyup blur", function() {
            t.checkLength.call(t, this)
        })
    }, i.prototype.showCount = function() {
        this.options.placement ? this.$counter.appendTo(this.options.placement) : this.$counter.insertAfter(this.element), this.checkLength()
    }, i.prototype.updateCountData = function(t) {
        var i = t + this.options.separator + this.options.limit;
        this.$counter.text(i)
    }, i.prototype.checkLength = function() {
        var t = this.element.val(),
            i = t.length;
        i > this.options.limit && (this.element.val(t.substring(0, this.options.limit)), i = this.options.limit), this.updateCountData(i)
    }, n.fn.EnsureMaxLength = function(t) {
        return this.each(function() {
            n.data(this, "plugin") || n.data(this, "plugin", new i(this, t))
        })
    }
}(jQuery);
