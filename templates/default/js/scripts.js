// Tooltips

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

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

