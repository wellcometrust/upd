(function ($) {
  /**
   * Set active class on Views AJAX filter
   * on selected category
   *
   * Credit: http://leanderlindahl.se/article/filter-content-drupal-view-ajax
   */
  Drupal.behaviors.exposedfilter_faq_buttons = {
    attach: function (context, settings) {
      var filter_link = $('.faq-cat-changer');
      filter_link.on('click', function (e) {
        e.preventDefault();

        // Get ID of clicked item
        var id = $(e.target).attr('data-term-id');

        // Set the new value in the SELECT element
        var filter = $('#views-exposed-form-faqs-faqs' + ' select[name="field_faq_category_target_id"]');
        filter.val(id);

        // Unset and then set the active class
        filter_link.removeClass('active');
        $(e.target).addClass('active');

        // Do it! Trigger the select box
        //filter.trigger('change');
        filter.trigger('change');
        $('#views-exposed-form-faqs-faqs input.form-submit').trigger('click');

      });
    }
  };

  jQuery(document).ajaxComplete(function (event, xhr, settings) {
    switch (settings.extraData.view_name) {
      case "faqs":
        var filter_id = $('#views-exposed-form-faqs-faqs' + ' select[name="field_faq_category_target_id"]').find(":selected").attr('value');
        $('#views-exposed-form-faqs-faqs a').removeClass('active');
        $('*[data-term-id="' + filter_id + '"]').addClass('active');

        break;

      default:
        break;
    }
  });
})(jQuery);