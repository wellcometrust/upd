<?php

namespace Drupal\config_ignore\Tests;

use Drupal\config_ignore\ConfigImporterIgnore;
use Drupal\simpletest\WebTestBase;
use Drupal\Core\Serialization\Yaml;

/**
 * Class ConfigIgnoreTest.
 *
 * @package Drupal\config_ignore\Tests
 * @group config_ignore
 */
class ConfigIgnoreTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['config_ignore'];

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
   * Verify that config can get ignored.
   */
  public function testValidateIgnoring() {

    // Login with a user that has permission to import config.
    $this->drupalLogin($this->drupalCreateUser(['import configuration']));

    // Set the site name to a known value that we later will try and overwrite.
    $this->config('system.site')->set('name', 'Test import')->save();

    // Set the system.site:name to be ignored upon config import.
    $this->config('config_ignore.settings')->set('ignored_config_entities', ['system.site'])->save();

    // Assemble a change that will try and override the current value.
    $config = $this->config('system.site')->set('name', 'Import has changed title');

    $edit = [
      'config_type' => 'system.simple',
      'config_name' => $config->getName(),
      'import' => Yaml::encode($config->get()),
    ];

    // Submit a new single item config, with the changes.
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));

    $this->drupalPostForm(NULL, array(), t('Confirm'));

    // Validate if the title from the imported config was rejected.
    $this->assertText('Test import');

    // Validate that the user gets a message about what has been ignored.
    $this->assertText('The following config entity was ignored');

  }

  /**
   * Verify all wildcard asterisk is working.
   */
  public function testValidateIgnoringWithWildcard() {
    // Login with a user that has permission to import config.
    $this->drupalLogin($this->drupalCreateUser(['import configuration']));

    // Set the site name to a known value that we later will try and overwrite.
    $this->config('system.site')->set('name', 'Test import')->save();

    // Set the system.site:name to be ignored using system.*.
    $this->config('config_ignore.settings')->set('ignored_config_entities', ['system.' . ConfigImporterIgnore::INCLUDE_SUFFIX])->save();

    // Assemble a change that will try and override the current value.
    $config = $this->config('system.site')->set('name', 'Import has changed title');

    $edit = [
      'config_type' => 'system.simple',
      'config_name' => $config->getName(),
      'import' => Yaml::encode($config->get()),
    ];

    // Submit a new single item config, with the changes.
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));

    $this->drupalPostForm(NULL, array(), t('Confirm'));

    // Validate if the title from the imported config was rejected.
    $this->assertText('Test import');

    // Validate that the user gets a message about what has been ignored.
    $this->assertText('The following config entity was ignored');

    // Run the same test, but with *.site.
    $this->config('config_ignore.settings')->set('ignored_config_entities', [ConfigImporterIgnore::INCLUDE_SUFFIX . '.site'])->save();

    // Assemble a change that will try and override the current value.
    $config = $this->config('system.site')->set('name', 'Import has changed title');

    $edit = [
      'config_type' => 'system.simple',
      'config_name' => $config->getName(),
      'import' => Yaml::encode($config->get()),
    ];

    // Submit a new single item config, with the changes.
    $this->drupalPostForm('admin/config/development/configuration/single/import', $edit, t('Import'));

    $this->drupalPostForm(NULL, array(), t('Confirm'));

    // Validate if the title from the imported config was rejected.
    $this->assertText('Test import');

    // Validate that the user gets a message about what has been ignored.
    $this->assertText('The following config entity was ignored');

  }

  /**
   * Verify Force Import syntax is working.
   *
   * This test makes sure we avoid regression issues.
   */
  public function testValidateForceImporting() {

    // Login with a user that has permission to import config.
    $this->drupalLogin($this->drupalCreateUser(['import configuration']));

    // Set the site name to a known value that we later will try and overwrite.
    $this->config('system.site')->set('name', 'Test import')->save();

    // Set the system.site:name to be (force-) imported upon config import.
    $settings = [ConfigImporterIgnore::FORCE_EXCLUSION_PREFIX . 'system.site'];
    $this->config('config_ignore.settings')->set('ignored_config_entities', $settings)->save();

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

    // Validate if the title from the imported config was imported.
    $this->assertText('Import has changed title');

    // Validate that the user does NOT get the message about configuration
    // been ignored.
    $this->assertNoText('The following config entity was ignored');

  }

  /**
   * Verify excluded configuration works with wildcards.
   *
   * This test cover the scenario where a wildcard matches a specific
   * configuration, but that's still imported due exclusion.
   */
  public function testValidateForceImportingWithWildcard() {

    // Login with a user that has permission to import config.
    $this->drupalLogin($this->drupalCreateUser(['import configuration']));

    // Set the site name to a known value that we later will try and overwrite.
    $this->config('system.site')->set('name', 'Test import')->save();

    // Set system.* configs to be ignored and system.site:name to be (force-)
    // imported upon config import.
    $settings = [
      'system.' . ConfigImporterIgnore::INCLUDE_SUFFIX,
      ConfigImporterIgnore::FORCE_EXCLUSION_PREFIX . 'system.site',
    ];
    $this->config('config_ignore.settings')->set('ignored_config_entities', $settings)->save();

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

    // Validate if the title from the imported config was imported.
    $this->assertText('Import has changed title');

  }

}
