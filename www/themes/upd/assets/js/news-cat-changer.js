(function ($) {
  /**
   * Set active class on Views AJAX filter
   * on selected category
   *
   * Credit: http://leanderlindahl.se/article/filter-content-drupal-view-ajax
   */
  Drupal.behaviors.exposedfilter_buttons = {
    attach: function (context, settings) {
      var filter_link = $('.news-cat-changer');
      filter_link.on('click', function (e) {
        e.preventDefault();

        // Get ID of clicked item
        var id = $(e.target).attr('data-term-id');

        // Set the new value in the SELECT element
        var filter = $('#views-exposed-form-news-news' + ' select[name="field_news_category_target_id"]');
        filter.val(id);

        // Unset and then set the active class
        filter_link.removeClass('active');
        $(e.target).addClass('active');

        // Do it! Trigger the select box
        //filter.trigger('change');
        filter.trigger('change');
        $('#views-exposed-form-news-news input.form-submit').trigger('click');

      });
    }
  };

  jQuery(document).ajaxComplete(function (event, xhr, settings) {
    switch (settings.extraData.view_name) {
      case "news":
        var filter_id = $('#views-exposed-form-news-news' + ' select[name="field_news_category_target_id"]').find(":selected").attr('value');
        $('#views-exposed-form-news-news a').removeClass('active');
        $('*[data-term-id="' + filter_id + '"]').addClass('active');

        break;

      default:
        break;
    }
  });
})(jQuery);