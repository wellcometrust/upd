<?php

namespace Drupal\config_ignore\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Class ConfigIgnoreSettingsTest.
 *
 * @package Drupal\config_ignore\Tests
 * @group config_ignore
 */
class ConfigIgnoreSettingsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['config_ignore'];

  /**
   * Verify that the settings form works.
   */
  public function testSettingsForm() {
    // Login with a user that has permission to import config.
    $this->drupalLogin($this->drupalCreateUser(['import configuration']));

    $edit = [
      'ignored_config_entities' => 'config.test',
    ];

    $this->drupalPostForm('admin/config/development/configuration/ignore', $edit, t('Save configuration'));

    $settings = $this->config('config_ignore.settings')->get('ignored_config_entities');

    $this->assertEqual($settings, ['config.test']);
  }

}
