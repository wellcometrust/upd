(function ($) {
  $(".content a").filter(function() {
    return this.hostname &&
      this.hostname !== location.hostname &&
      this.hostname !== 'twitter.com' &&
      this.hostname !== 'facebook.com';
  }).addClass('external-link').attr('target','_blank');
})(jQuery);