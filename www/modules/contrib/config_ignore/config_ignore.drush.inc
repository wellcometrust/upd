<?php

/**
 * @file
 * Overloads some Drush config related commands.
 */

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\FileStorage;
use Drush\Config\StorageWrapper;
use Drupal\Core\Config\StorageComparer;
use Drupal\config_ignore\ConfigImporterIgnore;

/**
 * Print out a message regarding what config that will be ignored.
 *
 * There are some repeating operations, copied from `drush_config_import`, where
 * we figure out what config that has changed.
 *
 * @param string $source
 *   What config source to use.
 */
function drush_config_ignore_pre_config_import($source = NULL) {

  // Determine source directory.
  if ($target = drush_get_option('source')) {
    $source_dir = $target;
  }
  else {
    $source = CONFIG_SYNC_DIRECTORY;
    $source_dir = config_get_config_directory($source);
  }

  /** @var \Drupal\Core\Config\StorageInterface $active_storage */
  $active_storage = \Drupal::service('config.storage');

  $source_storage = new FileStorage($source_dir);

  $storage_filters = drush_config_get_storage_filters();
  if (!empty($storage_filters)) {
    $source_storage = new StorageWrapper($source_storage, $storage_filters);
  }

  /** @var ConfigManagerInterface $config_manager */
  $config_manager = \Drupal::service('config.manager');
  $storage_comparer = new StorageComparer($source_storage, $active_storage, $config_manager);
  $storage_comparer->createChangelist()->hasChanges();

  $change_list = array();
  foreach ($storage_comparer->getAllCollectionNames() as $collection) {
    $change_list[$collection] = $storage_comparer->getChangelist(NULL, $collection);
  }

  $update_changes = $change_list['']['update'];

  if (!empty($update_changes)) {

    $red = "\033[31;40m\033[1m%s\033[0m";
    $yellow = "\033[1;33;40m\033[1m%s\033[0m";
    $green = "\033[1;32;40m\033[1m%s\033[0m";

    drush_print();
    drush_print(sprintf($green, 'Message from Config Ignore'), 1);
    drush_print('The following list of config will be ignore if you chose to import', 1);
    foreach ($update_changes as $config_name) {
      if (ConfigImporterIgnore::matchConfigName($config_name)) {
        drush_print(sprintf($red, $config_name), 3);
      }
    }
    drush_print();
  }
}
