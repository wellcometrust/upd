/**
  * Short piece of code that shows extra fields when the user clicks in the
  * email sign up field.
 **/
(function ($) {

  Drupal.behaviors.mailchimpBlockFieldsToShow = {
    attach: function (context, settings) {
      if ($('#mce-EMAIL').length) {
        $('#mce-EMAIL').on('click', function() {
          $('#mc_embed_signup').find('input, div, fieldset, .form-checkbox+label').removeClass('visually-hidden');
        });
      }

      $(window).on('load', function() {
        $('#mc_embed_signup').find('input, div, fieldset, .form-checkbox+label').addClass('visually-hidden');
        $('#mce-EMAIL').removeClass('visually-hidden').parents('div').removeClass('visually-hidden');
        $('#mc_embed_signup .form-submit').removeClass('visually-hidden').parents('div').removeClass('visually-hidden');
      });
    }
  };

})(jQuery);
