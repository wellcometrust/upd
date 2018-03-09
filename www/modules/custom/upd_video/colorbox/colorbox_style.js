(function ($, Drupal) {

  "use strict";

  Drupal.behaviors.initColorboxPlainStyle = {
    attach: function (context, settings) {
      $(context).bind('cbox_complete', function () {
        // Make all the controls invisible.
        $('#cboxCurrent, #cboxSlideshow, #cboxPrevious, #cboxNext', context).addClass('element-invisible');
        // Replace "Close" with "×" and show.
        $('#cboxClose', context).addClass('icon--x');
        // Hide empty title.
        if ($('#cboxTitle:empty', context).length == true) {
          $('#cboxTitle', context).hide();
        }
      });
      $(context).bind('cbox_closed', function () {
        $('#cboxClose', context).removeClass('cbox-close-plain');
      });
    }
  };

})(jQuery, Drupal);
