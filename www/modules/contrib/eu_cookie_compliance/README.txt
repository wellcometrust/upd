EU Cookie Compliance 8.x - 1.x
==============================

This module intends to deal with the EU Directive on Privacy and Electronic
Communications that comes into effect on 26th May 2012.
From that date, if you are not compliant or visibly working towards compliance,
you run the risk of enforcement action, which can include a fine of up to
half a million pounds for a serious breach.

How it works.
=============

The module displays a banner at the bottom or the top of website to make
users aware of the fact that cookies are being set. The user may then give
his/her consent or move to a page that provides more details. Consent is given
by user pressing the agree buttons or by continuing browsing the website. Once
consent is given another banner appears with a "Thank you" message.

The module provides a settings page where the banner can be customized. There
are also template files for the banners that can be overridden by your theme.

Installation.
=============

1. Unzip the files to the "sites/all/modules" OR "modules" directory and enable
   the module.

2. If desired, give the administer EU Cookie Compliance banner permissions that
   allow users of certain roles access the administration page. You can do so on
   the admin/user/permissions page.

  - there is also a 'display eu cookie compliance banner' permission that helps
    you show the banner to the roles you desire.

3. You may want to create a page that would explain how your site uses cookies.
   Alternatively, if you have a privacy policy, you can link the banner to that
   page (see next step).

4. Go to the admin/config/system/eu-cookie-compliance page to configure and
   enable the banner.

5. If you want to customize the banner background and text color, either type
   in the hex values or simply install
   http://drupal.org/project/jquery_colorpicker.

6. If you want to theme your banner, override the themes in the template file.

7. If you want to show the message in EU countries only, install the Smart IP
   module: http://drupal.org/project/smart_ip and enable the option "Only
   display banner in EU countries" on the admin page. There is a JavaScript
   based option available for sites that use Varnish (or other caching
   strategies). The JavaScript based variant also works for visitors that bypass
   Varnish.

For developers.
===============

If you want to conditionally set cookies in your module, there is a javascript
function provided that returns TRUE if the current user has given his consent:

Drupal.eu_cookie_compliance.hasAgreed()

Prevent "Consent by clicking" for some links
============================================
The module offers a feature to accept consent by clicking. It may be relevant to
prevent this for certain links. In such cases, the link(s) can be wrapped in an
element with the class "popup-content".
