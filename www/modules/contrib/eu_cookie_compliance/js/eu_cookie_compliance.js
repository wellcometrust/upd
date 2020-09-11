/**
 * @file
 * eu_cookie_compliance.js
 *
 * Defines the behavior of the eu cookie compliance banner.
 *
 * Statuses:
 *  null: not yet agreed (or withdrawn), show popup
 *  0: Disagreed
 *  1: Agreed, show thank you banner
 *  2: Agreed
 */

(function ($, Drupal, drupalSettings) {

  'use strict';
  var euCookieComplianceBlockCookies;

  Drupal.behaviors.euCookieCompliancePopup = {
    attach: function (context) {
      $('body').once('eu-cookie-compliance').each(function () {
        // If configured, check JSON callback to determine if in EU.
        if (drupalSettings.eu_cookie_compliance.popup_eu_only_js) {
          if (Drupal.eu_cookie_compliance.showBanner()) {
            var url = drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'eu-cookie-compliance-check';
            var data = {};
            $.getJSON(url, data, function (data) {
              // If in the EU, show the compliance banner.
              if (data.in_eu) {
                Drupal.eu_cookie_compliance.execute();
              }

              // If not in EU, set an agreed cookie automatically.
              else {
                Drupal.eu_cookie_compliance.setStatus(2);
              }
            });
          }
        }

        // Otherwise, fallback to standard behavior which is to render the banner.
        else {
          Drupal.eu_cookie_compliance.execute();
        }
      });
    },
  };

  Drupal.eu_cookie_compliance = {};

  Drupal.eu_cookie_compliance.execute = function () {
    try {
      if (!drupalSettings.eu_cookie_compliance.popup_enabled) {
        return;
      }

      if (!Drupal.eu_cookie_compliance.cookiesEnabled()) {
        return;
      }

      var status = Drupal.eu_cookie_compliance.getCurrentStatus();
      if ((status === 0 && drupalSettings.eu_cookie_compliance.method === 'default') || status === null || (drupalSettings.eu_cookie_compliance.withdraw_enabled && drupalSettings.eu_cookie_compliance.withdraw_button_on_info_popup)) {
        if (!drupalSettings.eu_cookie_compliance.disagree_do_not_show_popup || status === null) {
          // Detect mobile here and use mobile_popup_html_info, if we have a mobile device.
          if (window.matchMedia('(max-width: ' + drupalSettings.eu_cookie_compliance.mobile_breakpoint + 'px)').matches && drupalSettings.eu_cookie_compliance.use_mobile_message) {
            Drupal.eu_cookie_compliance.createPopup(drupalSettings.eu_cookie_compliance.mobile_popup_html_info, (status !== null));
          } else {
            Drupal.eu_cookie_compliance.createPopup(drupalSettings.eu_cookie_compliance.popup_html_info, (status !== null));
          }
          Drupal.eu_cookie_compliance.initPopup();
        }
      }
      if (status === 1 && drupalSettings.eu_cookie_compliance.popup_agreed_enabled) {
        // Thank you banner.
        Drupal.eu_cookie_compliance.createPopup(drupalSettings.eu_cookie_compliance.popup_html_agreed);
        Drupal.eu_cookie_compliance.attachHideEvents();
      } else if (status === 2 && drupalSettings.eu_cookie_compliance.withdraw_enabled) {
        if (!drupalSettings.eu_cookie_compliance.withdraw_button_on_info_popup) {
          Drupal.eu_cookie_compliance.createWithdrawBanner(drupalSettings.eu_cookie_compliance.withdraw_markup);
        }
        Drupal.eu_cookie_compliance.attachWithdrawEvents();
      }
    }
    catch (e) {
    }
  };

  Drupal.eu_cookie_compliance.initPopup = function() {
    Drupal.eu_cookie_compliance.attachAgreeEvents();

    if (drupalSettings.eu_cookie_compliance.method === 'categories') {
      var categories_checked = [];

      if (Drupal.eu_cookie_compliance.getCurrentStatus() === null) {
        if (drupalSettings.eu_cookie_compliance.select_all_categories_by_default) {
          categories_checked = drupalSettings.eu_cookie_compliance.cookie_categories;
        }
      }
      else {
        categories_checked = Drupal.eu_cookie_compliance.getAcceptedCategories();
      }
      Drupal.eu_cookie_compliance.setPreferenceCheckboxes(categories_checked);
      Drupal.eu_cookie_compliance.attachSavePreferencesEvents();
    }

    if (drupalSettings.eu_cookie_compliance.withdraw_enabled && drupalSettings.eu_cookie_compliance.withdraw_button_on_info_popup) {
      Drupal.eu_cookie_compliance.attachWithdrawEvents();
      var currentStatus = Drupal.eu_cookie_compliance.getCurrentStatus();
      if (currentStatus === 1 || currentStatus === 2) {
        $('.eu-cookie-withdraw-button').show();
      }
    }
  }

  Drupal.eu_cookie_compliance.createWithdrawBanner = function (html) {
    var $html = $('<div></div>').html(html);
    var $banner = $('.eu-cookie-withdraw-banner', $html);
    $html.attr('id', 'sliding-popup');
    $html.addClass('eu-cookie-withdraw-wrapper');

    if (!drupalSettings.eu_cookie_compliance.popup_use_bare_css) {
      $banner.height(drupalSettings.eu_cookie_compliance.popup_height)
          .width(drupalSettings.eu_cookie_compliance.popup_width);
    }
    $html.hide();
    var height = 0;
    if (drupalSettings.eu_cookie_compliance.popup_position) {
      $html.prependTo('body');
      height = $html.outerHeight();

      $html.show()
          .addClass('sliding-popup-top')
          .addClass('clearfix')
        .css({ top: !drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top')) + height) : -1 * height });
      // For some reason, the tab outerHeight is -10 if we don't use a timeout
      // function to reveal the tab.
      setTimeout(function () {
        var height = $html.outerHeight();

        $html.animate({ top: !drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top')) + height) : -1 * height }, drupalSettings.eu_cookie_compliance.popup_delay, null, function () {
          $html.trigger('eu_cookie_compliance_popup_open');
        });
      }.bind($html), 0);
    } else {
      if (drupalSettings.eu_cookie_compliance.better_support_for_screen_readers) {
        $html.prependTo('body');
      } else {
        $html.appendTo('body');
      }
      height = $html.outerHeight();
      $html.show()
          .addClass('sliding-popup-bottom')
          .css({ bottom: -1 * height });
      // For some reason, the tab outerHeight is -10 if we don't use a timeout
      // function to reveal the tab.
      setTimeout(function () {
        var height = $html.outerHeight();

        $html.animate({ bottom: -1 * (height) }, drupalSettings.eu_cookie_compliance.popup_delay, null, function () {
          $html.trigger('eu_cookie_compliance_popup_open');
        });
      }.bind($html), 0);
    }
  };

  Drupal.eu_cookie_compliance.toggleWithdrawBanner = function () {
    var $wrapper = $('#sliding-popup');
    var $tab = $('.eu-cookie-withdraw-tab');
    var topBottom = (drupalSettings.eu_cookie_compliance.popup_position ? 'top' : 'bottom');
    var height = $wrapper.outerHeight();
    var $bannerIsShowing = drupalSettings.eu_cookie_compliance.popup_position ? parseInt($wrapper.css('top')) === (!drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top'))) : 0) : parseInt($wrapper.css('bottom')) === 0;
    if (drupalSettings.eu_cookie_compliance.popup_position) {
      if ($bannerIsShowing) {
        $wrapper.animate({ top: !drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top')) + height) : -1 * height}, drupalSettings.eu_cookie_compliance.popup_delay);
      }
      else {
        $wrapper.animate({ top: !drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top'))) : 0}, drupalSettings.eu_cookie_compliance.popup_delay);
      }
    }
    else {
      if ($bannerIsShowing) {
        $wrapper.animate({'bottom' : -1 * (height)}, drupalSettings.eu_cookie_compliance.popup_delay);
      }
      else {
        $wrapper.animate({'bottom' : 0}, drupalSettings.eu_cookie_compliance.popup_delay);
      }
    }
  };

  Drupal.eu_cookie_compliance.createPopup = function (html, closed) {
    // This fixes a problem with jQuery 1.9.
    var popup = $('<div></div>').html(html);
    popup.attr('id', 'sliding-popup');
    if (!drupalSettings.eu_cookie_compliance.popup_use_bare_css) {
      popup.height(drupalSettings.eu_cookie_compliance.popup_height)
          .width(drupalSettings.eu_cookie_compliance.popup_width);
    }

    popup.hide();
    var height = 0;
    if (drupalSettings.eu_cookie_compliance.popup_position) {
      popup.prependTo('body');
      height = popup.outerHeight();
      popup.show()
        .addClass('sliding-popup-top clearfix')
        .css({ top: -1 * height });
      if (closed !== true) {
        popup.animate({top: 0}, drupalSettings.eu_cookie_compliance.popup_delay, null, function () {
          popup.trigger('eu_cookie_compliance_popup_open');
        });
      }
    } else {
      if (drupalSettings.eu_cookie_compliance.better_support_for_screen_readers) {
        popup.prependTo('body');
      } else {
        popup.appendTo('body');
      }

      height = popup.outerHeight();
      popup.show()
        .addClass('sliding-popup-bottom')
        .css({bottom: -1 * height});
      if (closed !== true) {
        popup.animate({bottom: 0}, drupalSettings.eu_cookie_compliance.popup_delay, null, function () {
          popup.trigger('eu_cookie_compliance_popup_open');
        });
      }
    }
  };

  Drupal.eu_cookie_compliance.attachAgreeEvents = function () {
    var clickingConfirms = drupalSettings.eu_cookie_compliance.popup_clicking_confirmation;
    var scrollConfirms = drupalSettings.eu_cookie_compliance.popup_scrolling_confirmation;

    if (drupalSettings.eu_cookie_compliance.method === 'categories' && drupalSettings.eu_cookie_compliance.enable_save_preferences_button) {
        // The agree button becomes an agree to all categories button when the 'save preferences' button is present.
        $('.agree-button').click(Drupal.eu_cookie_compliance.acceptAllAction);
    }
    else {
        $('.agree-button').click(Drupal.eu_cookie_compliance.acceptAction);
    }
    $('.decline-button').click(Drupal.eu_cookie_compliance.declineAction);

    if (clickingConfirms) {
      $('a, input[type=submit], button[type=submit]').not('.popup-content *').bind('click.euCookieCompliance', Drupal.eu_cookie_compliance.acceptAction);
    }

    if (scrollConfirms) {
      var alreadyScrolled = false;
      var scrollHandler = function () {
        if (alreadyScrolled) {
          Drupal.eu_cookie_compliance.acceptAction();
          $(window).off('scroll', scrollHandler);
        } else {
          alreadyScrolled = true;
        }
      };

      $(window).bind('scroll', scrollHandler);
    }

    $('.find-more-button').not('.find-more-button-processed').addClass('find-more-button-processed').click(Drupal.eu_cookie_compliance.moreInfoAction);
  };

  Drupal.eu_cookie_compliance.attachSavePreferencesEvents = function () {
    $('.eu-cookie-compliance-save-preferences-button').click(Drupal.eu_cookie_compliance.savePreferencesAction);
  };

  Drupal.eu_cookie_compliance.attachHideEvents = function () {
    var popupHideAgreed = drupalSettings.eu_cookie_compliance.popup_hide_agreed;
    var clickingConfirms = drupalSettings.eu_cookie_compliance.popup_clicking_confirmation;
    $('.hide-popup-button').click(function () {
          Drupal.eu_cookie_compliance.changeStatus(2);
        }
    );
    if (clickingConfirms) {
      $('a, input[type=submit], button[type=submit]').unbind('click.euCookieCompliance');
    }

    if (popupHideAgreed) {
      $('a, input[type=submit], button[type=submit]').bind('click.euCookieComplianceHideAgreed', function () {
        Drupal.eu_cookie_compliance.changeStatus(2);
      });
    }

    $('.find-more-button').not('.find-more-button-processed').addClass('find-more-button-processed').click(Drupal.eu_cookie_compliance.moreInfoAction);
  };

  Drupal.eu_cookie_compliance.attachWithdrawEvents = function () {
    $('.eu-cookie-withdraw-button').click(Drupal.eu_cookie_compliance.withdrawAction);
    $('.eu-cookie-withdraw-tab').click(Drupal.eu_cookie_compliance.toggleWithdrawBanner);
  };

  Drupal.eu_cookie_compliance.acceptAction = function () {
    var agreedEnabled = drupalSettings.eu_cookie_compliance.popup_agreed_enabled;
    var nextStatus = 1;
    if (!agreedEnabled) {
      Drupal.eu_cookie_compliance.setStatus(1);
      nextStatus = 2;
    }

    if (!euCookieComplianceHasLoadedScripts && typeof euCookieComplianceLoadScripts === "function") {
      euCookieComplianceLoadScripts();
    }

    if (typeof euCookieComplianceBlockCookies !== 'undefined') {
      clearInterval(euCookieComplianceBlockCookies);
    }

    if (drupalSettings.eu_cookie_compliance.method === 'categories') {
      // Select Checked categories.
      var categories = $("#eu-cookie-compliance-categories input:checkbox:checked").map(function(){
        return $(this).val();
      }).get();
      Drupal.eu_cookie_compliance.setAcceptedCategories(categories);
      // Load scripts for all categories.
      Drupal.eu_cookie_compliance.loadCategoryScripts(categories);
    }

    Drupal.eu_cookie_compliance.changeStatus(nextStatus);
  };

  Drupal.eu_cookie_compliance.acceptAllAction = function () {
    var allCategories = drupalSettings.eu_cookie_compliance.cookie_categories;
    Drupal.eu_cookie_compliance.setPreferenceCheckboxes(allCategories);
    Drupal.eu_cookie_compliance.acceptAction();
  }

  Drupal.eu_cookie_compliance.savePreferencesAction = function () {
    var categories = $("#eu-cookie-compliance-categories input:checkbox:checked").map(function(){
      return $(this).val();
    }).get();
    var agreedEnabled = drupalSettings.eu_cookie_compliance.popup_agreed_enabled;
    var nextStatus = 1;
    if (!agreedEnabled) {
      Drupal.eu_cookie_compliance.setStatus(1);
      nextStatus = 2;
    }

    Drupal.eu_cookie_compliance.setAcceptedCategories(categories);
    if (!euCookieComplianceHasLoadedScripts && typeof euCookieComplianceLoadScripts === "function") {
      euCookieComplianceLoadScripts();
    }
    Drupal.eu_cookie_compliance.loadCategoryScripts(categories);
    Drupal.eu_cookie_compliance.changeStatus(nextStatus);
  };

  Drupal.eu_cookie_compliance.loadCategoryScripts = function(categories) {
    for (var cat in categories) {
      if (euCookieComplianceHasLoadedScriptsForCategory[cat] !== true && typeof euCookieComplianceLoadScripts === "function") {
        euCookieComplianceLoadScripts(categories[cat]);
        euCookieComplianceHasLoadedScriptsForCategory[cat] = true;
      }
    }
  }

  Drupal.eu_cookie_compliance.declineAction = function () {
    Drupal.eu_cookie_compliance.setStatus(0);
    var popup = $('#sliding-popup');
    if (popup.hasClass('sliding-popup-top')) {
      popup.animate({ top: !drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top')) + popup.outerHeight()) : popup.outerHeight() * -1 }, drupalSettings.eu_cookie_compliance.popup_delay, null, function () {
        popup.hide();
      }).trigger('eu_cookie_compliance_popup_close');
    }
    else {
      popup.animate({ bottom: popup.outerHeight() * -1 }, drupalSettings.eu_cookie_compliance.popup_delay, null, function () {
        popup.hide();
      }).trigger('eu_cookie_compliance_popup_close');
    }
  };

  Drupal.eu_cookie_compliance.withdrawAction = function () {
    Drupal.eu_cookie_compliance.setStatus(0);
    Drupal.eu_cookie_compliance.setAcceptedCategories([]);
    location.reload();
  };

  Drupal.eu_cookie_compliance.moreInfoAction = function () {
    if (drupalSettings.eu_cookie_compliance.disagree_do_not_show_popup) {
      Drupal.eu_cookie_compliance.setStatus(0);
      if (drupalSettings.eu_cookie_compliance.withdraw_enabled && drupalSettings.eu_cookie_compliance.withdraw_button_on_info_popup) {
        $('#sliding-popup .eu-cookie-compliance-banner').trigger('eu_cookie_compliance_popup_close').hide();
      }
      else {
        $('#sliding-popup').trigger('eu_cookie_compliance_popup_close').remove();
      }
    } else {
      if (drupalSettings.eu_cookie_compliance.popup_link_new_window) {
        window.open(drupalSettings.eu_cookie_compliance.popup_link);
      } else {
        window.location.href = drupalSettings.eu_cookie_compliance.popup_link;
      }
    }
  };

  Drupal.eu_cookie_compliance.getCurrentStatus = function () {
    var cookieName = (typeof drupalSettings.eu_cookie_compliance.cookie_name === 'undefined' || drupalSettings.eu_cookie_compliance.cookie_name === '') ? 'cookie-agreed' : drupalSettings.eu_cookie_compliance.cookie_name;
    var value = $.cookie(cookieName);
    value = parseInt(value);
    if (isNaN(value)) {
      value = null;
    }

    return value;
  };

  Drupal.eu_cookie_compliance.setPreferenceCheckboxes = function (categories) {
    for (var i in categories) {
      $("#eu-cookie-compliance-categories input:checkbox[value='" + categories[i] + "']").prop("checked", true);
    }
  }

  Drupal.eu_cookie_compliance.getAcceptedCategories = function () {
    var allCategories = drupalSettings.eu_cookie_compliance.cookie_categories;
    var cookieName = (typeof drupalSettings.eu_cookie_compliance.cookie_name === 'undefined' || drupalSettings.eu_cookie_compliance.cookie_name === '') ? 'cookie-agreed-categories' : drupalSettings.eu_cookie_compliance.cookie_name + '-categories';
    var value = $.cookie(cookieName);
    var selectedCategories = [];

    if (value !== null && typeof value !== 'undefined') {
      value = JSON.parse(value);
      selectedCategories = value;
    }

    if (Drupal.eu_cookie_compliance.fix_first_cookie_category && !$.inArray(allCategories[0], selectedCategories)) {
      selectedCategories.push(allCategories[0]);
    }

    return selectedCategories;
  };

  Drupal.eu_cookie_compliance.changeStatus = function (value) {
    var status = Drupal.eu_cookie_compliance.getCurrentStatus();
    var reloadPage = drupalSettings.eu_cookie_compliance.reload_page;
    if (status === value) {
      return;
    }

    if (drupalSettings.eu_cookie_compliance.popup_position) {
      $('.sliding-popup-top').animate({ top: !drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top')) + $('#sliding-popup').outerHeight()) : $('#sliding-popup').outerHeight() * -1 }, drupalSettings.eu_cookie_compliance.popup_delay, function () {
        if (value === 1 && status === null && !reloadPage) {
          $('.sliding-popup-top').not('.eu-cookie-withdraw-wrapper').html(drupalSettings.eu_cookie_compliance.popup_html_agreed).animate({ top: !drupalSettings.eu_cookie_compliance.fixed_top_position ? -(parseInt($('body').css('padding-top')) + parseInt($('body').css('margin-top'))) : 0 }, drupalSettings.eu_cookie_compliance.popup_delay);
          Drupal.eu_cookie_compliance.attachHideEvents();
        } else if (status === 1 && !(drupalSettings.eu_cookie_compliance.withdraw_enabled && drupalSettings.eu_cookie_compliance.withdraw_button_on_info_popup)) {
          $('.sliding-popup-top').not('.eu-cookie-withdraw-wrapper').trigger('eu_cookie_compliance_popup_close').remove();
        }
        Drupal.eu_cookie_compliance.showWithdrawBanner(value);
      });
    } else {
      $('.sliding-popup-bottom').animate({ bottom: $('#sliding-popup').outerHeight() * -1 }, drupalSettings.eu_cookie_compliance.popup_delay, function () {
        if (value === 1 && status === null && !reloadPage) {
          $('.sliding-popup-bottom').not('.eu-cookie-withdraw-wrapper').html(drupalSettings.eu_cookie_compliance.popup_html_agreed).animate({ bottom: 0 }, drupalSettings.eu_cookie_compliance.popup_delay);
          Drupal.eu_cookie_compliance.attachHideEvents();
        } else if (status === 1) {
          if (drupalSettings.eu_cookie_compliance.withdraw_enabled && drupalSettings.eu_cookie_compliance.withdraw_button_on_info_popup) {
            // Restore popup content.
            if (window.matchMedia('(max-width: ' + drupalSettings.eu_cookie_compliance.mobile_breakpoint + 'px)').matches && drupalSettings.eu_cookie_compliance.use_mobile_message) {
              $('.sliding-popup-bottom').not('.eu-cookie-withdraw-wrapper').html(drupalSettings.eu_cookie_compliance.mobile_popup_html_info);
            } else {
              $('.sliding-popup-bottom').not('.eu-cookie-withdraw-wrapper').html(drupalSettings.eu_cookie_compliance.popup_html_info);
            }
            Drupal.eu_cookie_compliance.initPopup();
          }
          else {
            $('.sliding-popup-bottom').not('.eu-cookie-withdraw-wrapper').trigger('eu_cookie_compliance_popup_close').remove();
          }
        }
        Drupal.eu_cookie_compliance.showWithdrawBanner(value);
      });
    }

    if (drupalSettings.eu_cookie_compliance.reload_page) {
      location.reload();
    }

    Drupal.eu_cookie_compliance.setStatus(value);
  };

  Drupal.eu_cookie_compliance.showWithdrawBanner = function (value) {
    if (value === 2 && drupalSettings.eu_cookie_compliance.withdraw_enabled) {
      if (!drupalSettings.eu_cookie_compliance.withdraw_button_on_info_popup) {
        Drupal.eu_cookie_compliance.createWithdrawBanner(drupalSettings.eu_cookie_compliance.withdraw_markup);
      }
      Drupal.eu_cookie_compliance.attachWithdrawEvents();
    }
  };

  Drupal.eu_cookie_compliance.setStatus = function (status) {
    var date = new Date();
    var domain = drupalSettings.eu_cookie_compliance.domain ? drupalSettings.eu_cookie_compliance.domain : '';
    var path = drupalSettings.eu_cookie_compliance.domain_all_sites ? '/' : drupalSettings.path.baseUrl;
    var cookieName = (typeof drupalSettings.eu_cookie_compliance.cookie_name === 'undefined' || drupalSettings.eu_cookie_compliance.cookie_name === '') ? 'cookie-agreed' : drupalSettings.eu_cookie_compliance.cookie_name;
    if (path.length > 1) {
      var pathEnd = path.length - 1;
      if (path.lastIndexOf('/') === pathEnd) {
        path = path.substring(0, pathEnd);
      }
    }

    var cookie_session = parseInt(drupalSettings.eu_cookie_compliance.cookie_session);
    if (cookie_session) {
      $.cookie(cookieName, status, { path: path, domain: domain });
    } else {
      var lifetime = parseInt(drupalSettings.eu_cookie_compliance.cookie_lifetime);
      date.setDate(date.getDate() + lifetime);
      $.cookie(cookieName, status, { expires: date, path: path, domain: domain });
    }
    $(document).trigger('eu_cookie_compliance.changeStatus', [status]);

    // Store consent if applicable.
    if (drupalSettings.eu_cookie_compliance.store_consent && ((status === 1 && drupalSettings.eu_cookie_compliance.popup_agreed_enabled) || (status === 2  && !drupalSettings.eu_cookie_compliance.popup_agreed_enabled))) {
      var url = drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'eu-cookie-compliance/store_consent/banner';
      $.post(url, {}, function (data) { });
    }
  };

  Drupal.eu_cookie_compliance.setAcceptedCategories = function (categories) {
    var date = new Date();
    var domain = drupalSettings.eu_cookie_compliance.domain ? drupalSettings.eu_cookie_compliance.domain : '';
    var path = drupalSettings.eu_cookie_compliance.domain_all_sites ? '/' : drupalSettings.path.baseUrl;
    var cookieName = (typeof drupalSettings.eu_cookie_compliance.cookie_name === 'undefined' || drupalSettings.eu_cookie_compliance.cookie_name === '') ? 'cookie-agreed-categories' : drupalSettings.eu_cookie_compliance.cookie_name + '-categories';
    if (path.length > 1) {
      var pathEnd = path.length - 1;
      if (path.lastIndexOf('/') === pathEnd) {
        path = path.substring(0, pathEnd);
      }
    }
    var categoriesString = JSON.stringify(categories);
    var cookie_session = parseInt(drupalSettings.eu_cookie_compliance.cookie_session);
    if (cookie_session) {
      $.cookie(cookieName, categoriesString, { path: path, domain: domain });
    } else {
      var lifetime = parseInt(drupalSettings.eu_cookie_compliance.cookie_lifetime);
      date.setDate(date.getDate() + lifetime);
      $.cookie(cookieName, categoriesString, { expires: date, path: path, domain: domain });
    }
    $(document).trigger('eu_cookie_compliance.changePreferences', [categories]);

    // TODO: Store categories with consent if applicable?
  };

  Drupal.eu_cookie_compliance.hasAgreed = function (category) {
    var status = Drupal.eu_cookie_compliance.getCurrentStatus();
    var agreed = (status === 1 || status === 2);

    if(category !== undefined && agreed) {
      agreed = Drupal.eu_cookie_compliance.hasAgreedWithCategory(category);
    }

    return agreed;
  };

  Drupal.eu_cookie_compliance.hasAgreedWithCategory = function(category) {
    var allCategories = drupalSettings.eu_cookie_compliance.cookie_categories;
    var agreedCategories = Drupal.eu_cookie_compliance.getAcceptedCategories();

    if (drupalSettings.eu_cookie_compliance.fix_first_cookie_category && category === allCategories[0]) {
      return true;
    }

    return $.inArray(category, agreedCategories) !== -1;
  };

  Drupal.eu_cookie_compliance.showBanner = function () {
    var showBanner = false;
    var status = Drupal.eu_cookie_compliance.getCurrentStatus();
    if ((status === 0 && drupalSettings.eu_cookie_compliance.method === 'default') || status === null) {
      if (!drupalSettings.eu_cookie_compliance.disagree_do_not_show_popup || status === null) {
        showBanner = true;
      }
    } else if (status === 1 && drupalSettings.eu_cookie_compliance.popup_agreed_enabled) {
      showBanner = true;
    }

    return showBanner;
  };

  Drupal.eu_cookie_compliance.cookiesEnabled = function () {
    var cookieEnabled = (navigator.cookieEnabled);
    if (typeof navigator.cookieEnabled === 'undefined' && !cookieEnabled) {
      $.cookie('testcookie', 'testcookie', { expires: 100 });
      cookieEnabled = ($.cookie('testcookie').indexOf('testcookie') !== -1);
    }

    return (cookieEnabled);
  };

  Drupal.eu_cookie_compliance.isWhitelisted = function (cookieName) {
    // Skip the PHP session cookie.
    if (cookieName.indexOf('SESS') === 0 || cookieName.indexOf('SSESS') === 0) {
      return true;
    }
    // Split the white-listed cookies.
    var euCookieComplianceWhitelist = drupalSettings.eu_cookie_compliance.whitelisted_cookies.split(/\r\n|\n|\r/g);

    // Add the EU Cookie Compliance cookie.
    euCookieComplianceWhitelist.push((typeof drupalSettings.eu_cookie_compliance.cookie_name === 'undefined' || drupalSettings.eu_cookie_compliance.cookie_name === '') ? 'cookie-agreed' : drupalSettings.eu_cookie_compliance.cookie_name);
    euCookieComplianceWhitelist.push((typeof drupalSettings.eu_cookie_compliance.cookie_name === 'undefined' || drupalSettings.eu_cookie_compliance.cookie_name === '') ? 'cookie-agreed-categories' : drupalSettings.eu_cookie_compliance.cookie_name + '-categories');

    // Check if the cookie is white-listed.
    for (var item in euCookieComplianceWhitelist) {
      if (cookieName === euCookieComplianceWhitelist[item]) {
        return true;
      }
      // Handle cookie names that are prefixed with a category.
      if (drupalSettings.eu_cookie_compliance.method === 'categories') {
        var separatorPos = euCookieComplianceWhitelist[item].indexOf(":");
        if (separatorPos !== -1) {
          var category = euCookieComplianceWhitelist[item].substr(0, separatorPos);
          var wlCookieName = euCookieComplianceWhitelist[item].substr(separatorPos + 1);

          if (wlCookieName === cookieName && Drupal.eu_cookie_compliance.hasAgreedWithCategory(category)) {
            return true;
          }
        }
      }
    }

    return false;
  }

  // Load blocked scripts if the user has agreed to being tracked.
  var euCookieComplianceHasLoadedScripts = false;
  var euCookieComplianceHasLoadedScriptsForCategory = [];
  $(function () {
    if (Drupal.eu_cookie_compliance.hasAgreed()
        || (Drupal.eu_cookie_compliance.getCurrentStatus() === null && drupalSettings.eu_cookie_compliance.method !== 'opt_in' && drupalSettings.eu_cookie_compliance.method !== 'categories')
    ) {
      if (typeof euCookieComplianceLoadScripts === "function") {
        euCookieComplianceLoadScripts();
      }
      euCookieComplianceHasLoadedScripts = true;

      if (drupalSettings.eu_cookie_compliance.method === 'categories') {
        var acceptedCategories = Drupal.eu_cookie_compliance.getAcceptedCategories();
        Drupal.eu_cookie_compliance.loadCategoryScripts(acceptedCategories);
      }
    }
  });

  // Block cookies when the user hasn't agreed.
  if ((drupalSettings.eu_cookie_compliance.method === 'opt_in' && (Drupal.eu_cookie_compliance.getCurrentStatus() === null  || !Drupal.eu_cookie_compliance.hasAgreed()))
      || (drupalSettings.eu_cookie_compliance.method === 'opt_out' && !Drupal.eu_cookie_compliance.hasAgreed() && Drupal.eu_cookie_compliance.getCurrentStatus() !== null)
      || (drupalSettings.eu_cookie_compliance.method === 'categories')
  ) {
    var euCookieComplianceBlockCookies = setInterval(function () {
      // Load all cookies from jQuery.
      var cookies = $.cookie();

      // Check each cookie and try to remove it if it's not white-listed.
      for (var i in cookies) {
        var remove = true;
        var hostname = window.location.hostname;
        var cookieRemoved = false;
        var index = 0;

        remove = !Drupal.eu_cookie_compliance.isWhitelisted(i);

        // Remove the cookie if it's not white-listed.
        if (remove) {
          while (!cookieRemoved && hostname !== '') {
            // Attempt to remove.
            cookieRemoved = $.removeCookie(i, { domain: '.' + hostname, path: '/' });
            if (!cookieRemoved) {
              cookieRemoved = $.removeCookie(i, { domain: hostname, path: '/' });
            }

            index = hostname.indexOf('.');

            // We can be on a sub-domain, so keep checking the main domain as well.
            hostname = (index === -1) ? '' : hostname.substring(index + 1);
          }

          // Some jQuery Cookie versions don't remove cookies well.  Try again
          // using plain js.
          if (!cookieRemoved) {
            document.cookie = i + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;';
          }
        }
      }
    }, 5000);
  }

})(jQuery, Drupal, drupalSettings);
