<?php

namespace Drupal\config_ignore\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Core\Serialization\Yaml;

/**
 * Test hooks that this module implements.
 *
 * This must be a simpletest test as it does not seem that the PHPUnit
 * implementation for Drupal supports batch processing yet.
 *
 * @package Drupal\config_ignore\Tests
 *
 * @group config_ignore
 */
class ConfigIgnoreHookTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['config_ignore_hook_test'];

  /**
   * The profile to install as a basis for testing.
   *
   * We need to change it form the standard 'testing' profile as that will not
   * print the title on the page, which we use for testing.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Verify that hook_config_ignore_settings_alter are getting called.
   */
  public function testSettingsAlterHook() {

    // Login with a user that has permission to import config.
    $this->drupalLogin($this->drupalCreateUser(['import configuration']));

    // Set the site name to a known value that we later will try and overwrite.
    $this->config('system.site')->set('name', 'Test import title')->save();

    // Assemble a change that will try and override the current value.
    $config = $this->config('system.site')->set('name', 'Import has changed title');

    $edit = [
      'config_type' => 'system.simple',
      'config_name' => $config->getName(),
      'import' => Yaml::encode($config->get()),
    ];

    // Submit a new single item config, with the changes.
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));
    $this->drupalPostForm(NULL, [], t('Confirm'));

    // Validate if the title from the imported config was rejected, due to the
    // hook implemented in the `config_ignore_hook_test` module.
    $this->drupalGet('<front>');
    $this->assertText('Test import title');
  }

}
