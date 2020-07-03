<?php

/**
 * Include default settings.
 */
require __DIR__ . '/default.settings.php';

/**
 * Include dev settings.
 */
require DRUPAL_ROOT . '/sites/example.settings.local.php';

/**
 * Skip file system permissions hardening.
 *
 * The system module will periodically check the permissions of your site's
 * site directory to ensure that it is not writable by the website user. For
 * sites that are managed with a version control system, this can cause problems
 * when files in that directory such as settings.php are updated, because the
 * user pulling in the changes won't have permissions to modify files in the
 * directory.
 */
$settings['skip_permissions_hardening'] = TRUE;

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
 * @see https://www.drupal.org/project/config_ignore
 */
/*$config['config_ignore.settings']['ignored_config_entities'] = array(
  'devel.*',
  'kint.*',
  'webprofiler.*',
  'devel_generate.*',
);*/
$settings['hash_salt'] = 'ce-vm';
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
$config_directories['sync'] = '../config/sync';
$settings['trusted_host_patterns'] = [ '.*' ];

// Stage file proxy.
$config['stage_file_proxy.settings']['origin'] = 'https://understandingpatientdata.org.uk';

// Solr Config
$config['search_api.server.local_solr']= [
  'backend_config' => [
    'connector_config' => [
      'host' => '192.168.57.130',
      'path' => '/',
      'core' => 'upd_core',
      'port' => '8080',
    ],
  ],
];

/**
 * Include local dev settings if any (gitignored, normally).
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
   include __DIR__ . '/settings.local.php';
}
