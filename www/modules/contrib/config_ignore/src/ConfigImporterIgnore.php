<?php

namespace Drupal\config_ignore;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigImporter;
use Drupal\user\SharedTempStore;

/**
 * Class ConfigImporterIgnore.
 *
 * @package Drupal\config_ignore
 */
class ConfigImporterIgnore {

  const FORCE_EXCLUSION_PREFIX = '~';
  const INCLUDE_SUFFIX = '*';

  /**
   * Gather config that we want to keep.
   *
   * Saves the values, that are to be ignored, so that we can put them back in
   * later on.
   *
   * @param array $context
   *   Context of the config import.
   * @param ConfigImporter $config_importer
   *   Config importer object.
   */
  public static function preImport(array &$context, ConfigImporter $config_importer) {
    $config_to_ignore = [];

    foreach (['delete', 'create', 'rename', 'update'] as $op) {
      // For now, we only support updates.
      if ($op == 'update') {
        foreach ($config_importer->getUnprocessedConfiguration($op) as $config) {
          if (self::matchConfigName($config)) {
            $config_to_ignore[$op][$config] = \Drupal::config($config)->getRawData();
          }
        }
      }
      // We do not support core.extension.
      unset($config_to_ignore[$op]['core.extension']);
    }

    /** @var SharedTempStore $temp_store */
    $temp_store = \Drupal::service('user.shared_tempstore')->get('config_ignore');
    $temp_store->set('config_to_ignore', $config_to_ignore);

    $context['finished'] = 1;
  }

  /**
   * Replace the overridden values with the original ones.
   *
   * @param array $context
   *   Context of the config import.
   * @param ConfigImporter $config_importer
   *   Config importer object.
   */
  public static function postImport(array &$context, ConfigImporter $config_importer) {
    /** @var SharedTempStore $temp_store */
    $temp_store = \Drupal::service('user.shared_tempstore')->get('config_ignore');
    $config_to_ignore = $temp_store->get('config_to_ignore');
    $config_names_ignored = [];
    foreach ($config_to_ignore as $op) {
      foreach ($op as $config_name => $config) {
        /** @var \Drupal\Core\Config\Config $config_to_restore */
        $config_to_restore = \Drupal::service('config.factory')->getEditable($config_name);
        $config_to_restore->setData($config)->save();
        $config_names_ignored[] = $config_name;
      }
    }
    $context['finished'] = 1;
    $temp_store->delete('config_to_ignore');

    // Inform about the config entities ignored.
    // We have two formats, one for browser output and one for terminal.
    if (!empty($config_names_ignored)) {

      // The list of names looks different depending on output medium.
      // If terminal (CLI), then no markup.
      if (php_sapi_name() == 'cli' || isset($_SERVER['argc']) && is_numeric($_SERVER['argc'] && $_SERVER['argc'] > 0)) {
        $names_list = "\n\r  " . implode("\n\r  ", $config_names_ignored);
      }
      else {
        $output = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          '#items' => $config_names_ignored,
        ];
        $names_list = render($output);
      }

      // `PluralTranslatableMarkup` does not seem to handle HTML as well as
      // plain t() does. It will not allow the <ul> list in the browser, and
      // renders the lists HTML as clear text.
      if (count($config_names_ignored) == 1) {
        $message = t('The following config entity was ignored: @list', ['@list' => $names_list]);
      }
      else {
        $message = t('The following @count config entities was ignored: @list', ['@count' => count($config_names_ignored), '@list' => $names_list]);
      }

      drupal_set_message($message, 'warning');
    }
  }

  /**
   * Match a config entity name against the list of ignored config entities.
   *
   * @param string $config_name
   *   The name of the config entity to match against all ignored entities.
   *
   * @return bool
   *   True, if the config entity is to be ignored, false otherwise.
   */
  public static function matchConfigName($config_name) {
    $config_ignore_settings = \Drupal::config('config_ignore.settings')->get('ignored_config_entities');
    \Drupal::moduleHandler()->invokeAll('config_ignore_settings_alter', [&$config_ignore_settings]);

    // If the string is an excluded config, don't ignore it.
    if (in_array(static::FORCE_EXCLUSION_PREFIX . $config_name, $config_ignore_settings, TRUE)) {
      return FALSE;
    }

    foreach ($config_ignore_settings as $config_ignore_setting) {
      // Test if the config_name is in the ignore list using a shell like
      // validation function to test the config_ignore_setting pattern.
      if (fnmatch($config_ignore_setting, $config_name)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
