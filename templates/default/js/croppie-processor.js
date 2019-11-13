var resize = $('#upload-profile').croppie({
    enableExif: true,
    enableOrientation: true,    
    viewport: { // Default { width: 100, height: 100, type: 'square' } 
      width: 240,
      height: 240,
      type: 'square' //square
    },
    boundary: {
      width: 250,
      height: 250
    }
}); 

var resize_cover = $('#upload-cover').croppie({
    enableExif: true,
    enableOrientation: true,    
    viewport: { // Default { width: 100, height: 100, type: 'square' } 
      width: 240,
      height: 90,
      type: 'square' //square
    },
    boundary: {
      width: 250,
      height: 100
    }
}); 

$('#prof-image').on('change', function () { 

  $('#upload-profile').show();
  $('.btn-upload-image').show();

  var reader = new FileReader();

  reader.onload = function (e) {
    resize.croppie('bind',{
      url: e.target.result
    }).then(function(){
      console.log('jQuery bind complete');
    });
  }

  reader.readAsDataURL(this.files[0]);

});

$('#cover-image').on('change', function () { 
  
  $('#upload-cover').show();
  $('.btn-upload-image').show();

  var reader = new FileReader();
  reader.onload = function (e) {
    resize_cover.croppie('bind',{
      url: e.target.result
    }).then(function(){
      console.log('jQuery bind complete');
    });
  }
  reader.readAsDataURL(this.files[0]);
});

function upload_action(user, type) {
  // type: 0 Profile
  // type: 1 Cover
  
  $('.btn-upload-image').html(spinner(1, 4, 2, 1));

  if (user != 0) {
    var uid = '&id='+user;
  } else {
    var uid = '';
  }

  if (type == 1) {
    resize.croppie('result', {
      type: 'canvas',
      size: {
        width: 1000
      }
    }).then(function (img) {
      $.ajax({
        url: site_url+"/connection/croppie_more.php?action=profile"+uid,
        type: "POST",
        data: {"ajax_image":img},
        success: function (data) {
          html = '<img src="' + img + '" width="270px" height="auto"/>';
          $("#preview-crop-profile").html(html);
          $("#profile-photo-message").html(data);
          $("#upload-profile").hide(); 
          $("#action-buttons").html('<div class="pt-2">&nbsp</div>');
        }
      });
    });
  } else { 
    resize_cover.croppie('result', {
      type: 'canvas',
      size: {
          width: 1500
      }
    }).then(function (img) {
      $.ajax({
        url: site_url+"/connection/croppie_more.php?action=cover"+uid,
        type: "POST",
        data: {"ajax_image":img},
        success: function (data) {
          html = '<img src="' + img + '" width="270px" height="auto"/>';
          $("#preview-crop-cover").html(html);
          $("#cover-photo-message").html(data);
          $("#upload-cover").hide(); 
          $("#action-buttons-2").html('<div class="pt-2">&nbsp</div>');
        }
      });
    });
  }
}  
