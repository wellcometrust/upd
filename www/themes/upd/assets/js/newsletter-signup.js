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
    }
  };

})(jQuery);
