(function($) {
  "use strict"; // Start of use strict
  $(document).ready(function () {
    refreshActivity()
  });
  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    e.preventDefault();
  });

  $('.notif-item').on('click', function(e) {
      let notif = $(this);
      let id = $(this).attr('data-text');
      $.ajax({
        url : window.location.origin+'/notifications/seen/'+id,
        type : 'GET',
        dataType : 'html',
        success : function(code_html, statut){
          notif.remove();
          let nb=parseInt($('#alertsDropdown').find('span').html())-1;
          if(nb === 0){
            $('#alertsDropdown').find('span').remove();
          }
          else{
            $('#alertsDropdown').find('span').html(nb)
          }
        }
      });
  });
  setInterval(function () {
    refreshActivity();
  }, 60000);
  function refreshActivity() {
    $.ajax({
      url : window.location.origin+'/session/refresh',
      type : 'POST',
      success : function(data){
        console.log(data)
      }
    });
  }
})(jQuery);
