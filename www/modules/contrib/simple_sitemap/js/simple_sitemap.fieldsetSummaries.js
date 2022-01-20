/**
 * @file
 * Attaches simple_sitemap behaviors to the entity form.
 */
(function ($, Drupal) {

  "use strict";

  Drupal.behaviors.simpleSitemapFieldsetSummaries = {
    attach: function (context) {
      $(context).find('.simple-sitemap-fieldset').drupalSetSummary(function (context) {
        var enabledVariants = [];

        $(context).find('input:radio:checked[data-simple-sitemap-label][value="1"]').each(function () {
          enabledVariants.push(this.dataset.simpleSitemapLabel);
        });

        if (enabledVariants.length > 0) {
          return Drupal.t('Included in sitemaps: ') + enabledVariants.join(', ');
        }
        else {
          return Drupal.t('Excluded from all sitemaps');
        }
      });
    }
  };

})(jQuery, Drupal);
