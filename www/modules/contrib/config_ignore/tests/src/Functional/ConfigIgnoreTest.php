<?php

namespace Drupal\Tests\config_ignore\Functional;

use Drupal\Core\Config\CachedStorage;
use Drupal\Core\Config\FileStorage;
use Drupal\Tests\BrowserTestBase;

/**
 * Class ConfigIgnoreTest.
 *
 * @package Drupal\Tests\config_ignore\Functional
 *
 * @group config_ignore
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ConfigIgnoreTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['config_ignore'];

  /**
   * Verify that the Sync. table gets update with appropriate ignore actions.
   */
  public function testSyncTableUpdate() {

    // Setup a config sync. dir with a, more or less,  know set of config
    // entities. This is a full blown export of yaml files, written to the disk.
    $this->config('system.site')->set('name', 'Test import')->save();
    $this->config('system.date')->set('first_day', '0')->save();
    $this->config('config_ignore.settings')->set('ignored_config_entities', ['system.site'])->save();
    $destination = CONFIG_SYNC_DIRECTORY;
    $destination_dir = config_get_config_directory($destination);
    /** @var CachedStorage $source_storage */
    $source_storage = \Drupal::service('config.storage');
    $destination_storage = new FileStorage($destination_dir);
    foreach ($source_storage->listAll() as $name) {
      $destination_storage->write($name, $source_storage->read($name));
    }

    // Login with a user that has permission to sync. config.
    $this->drupalLogin($this->drupalCreateUser(['synchronize configuration']));

    // Change the site name, which is supposed to look as an ignored change
    // in on the sync. page.
    $this->config('system.site')->set('name', 'Test import with changed title')->save();
    $this->config('system.date')->set('first_day', '1')->save();

    // Validate that the sync. table informs the user that the config will be
    // ignored.
    $this->drupalGet('admin/config/development/configuration');
    $this->assertSession()->responseContains('✔');
    $this->assertSession()->responseContains('✖');
  }

}
