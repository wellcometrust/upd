<?php

/**
 * @file
 * Include default settings for the local ce-vm development environment.
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

$databases['default']['default'] = [
  'database' => 'drupal',
  'username' => 'dev',
  'password' => 'dev',
  'prefix' => '',
  'host' => 'upd-mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

// Config sync directory.
$settings['config_sync_directory'] = '../config/sync';

// Private files folder, usually outside the web folder.
$settings['file_private_path'] = '../private';

// Enable the 'local' config_split and force other splits to be disabled.
$config['config_split.config_split.local']['status'] = TRUE;
$config['config_split.config_split.master']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;

// Other settings.
$settings['hash_salt'] = 'ce-vm';
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
$settings['trusted_host_patterns'] = ['.*'];

// @TODO: #49093 Fix Solr configuration locally and search engine on website.
// Development: Uncomment the following code to override search API solr server
// configuration, when using other config_split configurations.
/*$config['search_api.server.local_solr'] = [
  'backend_config' => [
    'connector_config' => [
      'host' => '192.168.57.130',
      'path' => '/',
      'core' => 'upd_core',
      'port' => '8080',
    ],
  ],
];*/

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
