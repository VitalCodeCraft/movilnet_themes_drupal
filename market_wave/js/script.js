(function ($, Drupal, once) {
  Drupal.behaviors.market_waveBehavior = {
    attach: function (context, settings) {
      once('market_waveBehavior', 'input.myCustomBehavior', context).forEach(function (element) {});
        // Apply the myCustomBehaviour effect to the elements only once.

        // $(document).ready(function() {
          // Optimalisation: Store the references outside the event handler:
          var $window = jQuery(window);
          var $menu_div = jQuery('.navbar.navbar-expand-lg');
          var $menu_open = jQuery('.menu-wrapper');

          function checkWidth() {
              var windowsize = $window.width();
              if (windowsize < 768) {
                  //if the window is greater than 440px wide then turn on jScrollPane..
                  $menu_div.addClass('mobile-menu');
                  $(".mobile-menu" ).on( "click", function () {
                    $menu_open.toggleClass('open shadow');
                    console.log("asdc");
                  });
              } else {
                  $menu_div.removeClass('mobile-menu');
                  $menu_open.removeClass('open shadow');
              }
          }
          // Execute on load
          checkWidth();
          // Bind event listener
          $(window).resize(checkWidth);
      // });

    }
  };
})(jQuery, Drupal, once);
