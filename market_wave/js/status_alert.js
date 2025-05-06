(function ($, Drupal, once) {
    Drupal.behaviors.status_alert_Behavior = {
      attach: function (context, settings) {
        once('myCustomBehavior', 'input.myCustomBehavior', context).forEach(function (element) {
          // Apply the myCustomBehaviour effect to the elements only once.
        });
        const popup_exists = $(".status_alert_message .alert");
        if(popup_exists.length == 1){
            setTimeout(function () {
                $( ".status_alert_message .alert" ).remove();
            }, 10000);
        }
      }
    };
  })(jQuery, Drupal, once);
