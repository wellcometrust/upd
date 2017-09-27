$ = jQuery;
Drupal.behaviors.cktooltip = {
  attach: function (context, settings) {
    // Use attr tooltip for source text.
    $.tooltipster.on('init', function(event){
      event.instance.content($(event.origin).attr('tooltip'));
    });

    // Set up tooltip with options.
    $('[tooltip]').tooltipster({
      maxWidth: 275
    });
  }
};