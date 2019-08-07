<?php

/**
 * Include default settings.
 */
require __DIR__ . '/default.settings.php';

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'dev',
  'password' => 'dev',
  'prefix' => '',
  'host' => 'upd-mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

/**
 * VM settings
 */
$conf['advagg_admin_mode'] = 0;
$conf['advagg_font_fontfaceobserver'] = 6;
$conf['advagg_css_compressor'] = -1;
$conf['advagg_js_compressor'] = 0;
$conf['advagg_js_compress_inline'] = 0;

$conf['stage_file_proxy_origin'] = 'https://understandingpatientdata.org.uk';
$conf['https'] = TRUE;

$conf['cache'] = FALSE;

/**
 * Include local dev settings if any (gitignored, normally).
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
   include __DIR__ . '/settings.local.php';
}

$conf['drupal_http_request_fails'] = FALSE;
