<?php

namespace Drupal\Tests\imagemagick\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\file_mdm\FileMetadataInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests that Imagemagick integrates properly with File Metadata Manager.
 *
 * @group Imagemagick
 */
class ToolkitImagemagickFileMetadataTest extends BrowserTestBase {

  use TestFileCreationTrait;

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * A directory for image test file results.
   *
   * @var string
   */
  protected $testDirectory;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'simpletest',
    'file_test',
    'imagemagick',
    'file_mdm',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set the image factory.
    $this->imageFactory = $this->container->get('image.factory');

    // Prepare a directory for test file results.
    $this->testDirectory = 'public://imagetest';

    // Change the toolkit.
    \Drupal::configFactory()->getEditable('system.image')
      ->set('toolkit', 'imagemagick')
      ->save();
    \Drupal::configFactory()->getEditable('imagemagick.settings')
      ->set('debug', FALSE)
      ->set('binaries', 'imagemagick')
      ->set('quality', 100)
      ->save();

    // Set the toolkit on the image factory.
    $this->imageFactory->setToolkitId('imagemagick');
  }

  /**
   * Provides data for testFileMetadata.
   *
   * @return array[]
   *   A simple array of simple arrays, each having the following elements:
   *   - binaries to use for testing.
   *   - parsing method to use for testing.
   */
  public function providerFileMetadataTest() {
    return [
      ['imagemagick', 'imagemagick_identify'],
      ['graphicsmagick', 'imagemagick_identify'],
    ];
  }

  /**
   * Test image toolkit integration with file metadata manager.
   *
   * @param string $binaries
   *   The graphics package binaries to use for testing.
   * @param string $parsing_method
   *   The parsing method to use for testing.
   *
   * @dataProvider providerFileMetadataTest
   */
  public function testFileMetadata($binaries, $parsing_method) {
    $config = \Drupal::configFactory()->getEditable('imagemagick.settings');
    $config_mdm = \Drupal::configFactory()->getEditable('file_mdm.settings');

    // Reset file_mdm settings.
    $config_mdm
      ->set('metadata_cache.enabled', TRUE)
      ->set('metadata_cache.disallowed_paths', [])
      ->save();

    // Execute tests with selected binaries.
    // The test can only be executed if binaries are available on the shell
    // path.
    $config
      ->set('binaries', $binaries)
      ->set('use_identify', $parsing_method === 'imagemagick_identify')
      ->save();
    $status = \Drupal::service('image.toolkit.manager')->createInstance('imagemagick')->getExecManager()->checkPath('');
    if (!empty($status['errors'])) {
      // Bots running automated test on d.o. do not have binaries installed,
      // so the test will be skipped; it can be run locally where binaries are
      // installed.
      $this->markTestSkipped("Tests for '{$binaries}' cannot run because the binaries are not available on the shell path.");
    }

    // A list of files that will be tested.
    $files = [
      'public://image-test.png' => [
        'width' => 40,
        'height' => 20,
        'frames' => 1,
        'mimetype' => 'image/png',
        'colorspace' => 'SRGB',
        'profiles' => [],
      ],
      'public://image-test.gif' => [
        'width' => 40,
        'height' => 20,
        'frames' => 1,
        'mimetype' => 'image/gif',
        'colorspace' => 'SRGB',
        'profiles' => [],
      ],
      'dummy-remote://image-test.jpg' => [
        'width' => 40,
        'height' => 20,
        'frames' => 1,
        'mimetype' => 'image/jpeg',
        'colorspace' => 'SRGB',
        'profiles' => [],
      ],
      'public://test-multi-frame.gif' => [
        'skip_dimensions_check' => TRUE,
        'frames' => 13,
        'mimetype' => 'image/gif',
        'colorspace' => 'SRGB',
        'profiles' => [],
      ],
      'public://test-exif.jpeg' => [
        'skip_dimensions_check' => TRUE,
        'frames' => 1,
        'mimetype' => 'image/jpeg',
        'colorspace' => 'SRGB',
        'profiles' => ['exif'],
      ],
      'public://test-exif-icc.jpeg' => [
        'skip_dimensions_check' => TRUE,
        'frames' => 1,
        'mimetype' => 'image/jpeg',
        'colorspace' => 'SRGB',
        'profiles' => ['exif', 'icc'],
      ],
    ];

    // Setup a list of tests to perform on each type.
    $operations = [
      'resize' => [
        'function' => 'resize',
        'arguments' => ['width' => 20, 'height' => 10],
        'width' => 20,
        'height' => 10,
      ],
      'scale_x' => [
        'function' => 'scale',
        'arguments' => ['width' => 20],
        'width' => 20,
        'height' => 10,
      ],
      // Fuchsia background.
      'rotate_5' => [
        'function' => 'rotate',
        'arguments' => ['degrees' => 5, 'background' => '#FF00FF'],
        'width' => 41,
        'height' => 23,
      ],
      'convert_jpg' => [
        'function' => 'convert',
        'width' => 40,
        'height' => 20,
        'arguments' => ['extension' => 'jpeg'],
        'mimetype' => 'image/jpeg',
      ],
    ];

    // The file metadata manager service.
    $fmdm = $this->container->get('file_metadata_manager');

    // Prepare a copy of test files.
    $this->getTestFiles('image');
    file_unmanaged_copy(drupal_get_path('module', 'imagemagick') . '/misc/test-multi-frame.gif', 'public://', FILE_EXISTS_REPLACE);
    file_unmanaged_copy(drupal_get_path('module', 'imagemagick') . '/misc/test-exif.jpeg', 'public://', FILE_EXISTS_REPLACE);
    file_unmanaged_copy(drupal_get_path('module', 'imagemagick') . '/misc/test-exif-icc.jpeg', 'public://', FILE_EXISTS_REPLACE);

    // Perform tests without caching.
    $config_mdm->set('metadata_cache.enabled', FALSE)->save();
    foreach ($files as $source_uri => $source_image_data) {
      $this->assertFalse($fmdm->has($source_uri));
      $source_image_md = $fmdm->uri($source_uri);
      $this->assertTrue($fmdm->has($source_uri));
      $first = TRUE;
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up a fresh image.
        if ($first) {
          $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        $source_image = $this->imageFactory->get($source_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        $this->assertFalse($fmdm->has($saved_uri));

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from file.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
          $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
        }
        $fmdm->release($saved_uri);

        $first = FALSE;
      }
      $fmdm->release($source_uri);
      $this->assertFalse($fmdm->has($source_uri));
    }

    // Perform tests with caching.
    $config_mdm->set('metadata_cache.enabled', TRUE)->save();
    foreach ($files as $source_uri => $source_image_data) {
      $first = TRUE;
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up a fresh image.
        $this->assertFalse($fmdm->has($source_uri));
        $source_image_md = $fmdm->uri($source_uri);
        $this->assertTrue($fmdm->has($source_uri));
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        $source_image = $this->imageFactory->get($source_uri);
        if ($first) {
          // First time load, metadata loaded from file.
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          // Further loads, metadata loaded from cache.
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        $this->assertFalse($fmdm->has($saved_uri));

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service. Should be cached.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from cache.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $saved_image_md->isMetadataLoaded($parsing_method));
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
          $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
        }
        $fmdm->release($saved_uri);

        // We release the source image FileMetadata at each cycle to ensure
        // that metadata is read from cache.
        $fmdm->release($source_uri);
        $this->assertFalse($fmdm->has($source_uri));

        $first = FALSE;
      }
    }

    // Open source images again after deleting the temp folder files.
    // Source image data should now be cached, but temp files non existing.
    // Therefore we test that the toolkit can create a new temp file copy.
    // Note: on Windows, temp imagemagick file names have a
    // imaNNN.tmp.[image_extension] pattern so we cannot scan for
    // 'imagemagick'.
    $directory_scan = file_scan_directory('temporary://', '/ima.*/');
    $this->assertGreaterThan(0, count($directory_scan));
    foreach ($directory_scan as $file) {
      file_unmanaged_delete($file->uri);
    }
    $directory_scan = file_scan_directory('temporary://', '/ima.*/');
    $this->assertEquals(0, count($directory_scan));
    foreach ($files as $source_uri => $source_image_data) {
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up the source image. Parsing should be fully cached now.
        $fmdm->release($source_uri);
        $source_image_md = $fmdm->uri($source_uri);
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        $source_image = $this->imageFactory->get($source_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $source_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        $this->assertFalse($fmdm->has($saved_uri));

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service. Should be cached.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from cache.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $saved_image_md->isMetadataLoaded($parsing_method));
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
          $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
        }
        $fmdm->release($saved_uri);
      }
      $fmdm->release($source_uri);
      $this->assertFalse($fmdm->has($source_uri));
    }

    // Files in temporary:// must not be cached.
    if ($parsing_method === 'imagemagick_identify') {
      file_unmanaged_copy(drupal_get_path('module', 'imagemagick') . '/misc/test-multi-frame.gif', 'temporary://', FILE_EXISTS_REPLACE);
      $source_uri = 'temporary://test-multi-frame.gif';
      $fmdm->release($source_uri);
      $source_image_md = $fmdm->uri($source_uri);
      $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded('imagemagick_identify'));
      $source_image = $this->imageFactory->get($source_uri);
      $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded('imagemagick_identify'));
      $fmdm->release($source_uri);
      $source_image_md = $fmdm->uri($source_uri);
      $source_image = $this->imageFactory->get($source_uri);
      $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded('imagemagick_identify'));
    }

    // Invalidate cache, and open source images again. Now, all files should be
    // parsed again.
    Cache::InvalidateTags([
      'config:imagemagick.file_metadata_plugin.imagemagick_identify',
      'config:file_mdm.file_metadata_plugin.getimagesize',
    ]);
    // Disallow caching on the test results directory.
    $config_mdm->set('metadata_cache.disallowed_paths', ['public://imagetest/*'])->save();
    foreach ($files as $source_uri => $source_image_data) {
      $fmdm->release($source_uri);
    }
    foreach ($files as $source_uri => $source_image_data) {
      $this->assertFalse($fmdm->has($source_uri));
      $source_image_md = $fmdm->uri($source_uri);
      $this->assertTrue($fmdm->has($source_uri));
      $first = TRUE;
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up a fresh image.
        if ($first) {
          $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        $source_image = $this->imageFactory->get($source_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        $this->assertFalse($fmdm->has($saved_uri));

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
          $this->assertEquals($source_image_data['profiles'], $source_image->getToolkit()->getProfiles());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from file.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
          $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
        }
        $fmdm->release($saved_uri);

        $first = FALSE;
      }
      $fmdm->release($source_uri);
      $this->assertFalse($fmdm->has($source_uri));
    }
  }

  /**
   * Provides data for testFileMetadataLegacy.
   *
   * @return array[]
   *   A simple array of simple arrays, each having the following elements:
   *   - binaries to use for testing.
   *   - parsing method to use for testing.
   *
   * @todo remove in 8.x-3.0.
   */
  public function providerFileMetadataTestLegacy() {
    return [
      ['imagemagick', 'getimagesize'],
      ['graphicsmagick', 'getimagesize'],
    ];
  }

  /**
   * Test legacy image toolkit integration with file metadata manager.
   *
   * @param string $binaries
   *   The graphics package binaries to use for testing.
   * @param string $parsing_method
   *   The parsing method to use for testing.
   *
   * @todo remove in 8.x-3.0.
   *
   * @dataProvider providerFileMetadataTestLegacy
   *
   * @group legacy
   */
  public function testFileMetadataLegacy($binaries, $parsing_method) {
    $config = \Drupal::configFactory()->getEditable('imagemagick.settings');
    $config_mdm = \Drupal::configFactory()->getEditable('file_mdm.settings');

    // Reset file_mdm settings.
    $config_mdm
      ->set('metadata_cache.enabled', TRUE)
      ->set('metadata_cache.disallowed_paths', [])
      ->save();

    // Execute tests with selected binaries.
    // The test can only be executed if binaries are available on the shell
    // path.
    $config
      ->set('binaries', $binaries)
      ->set('use_identify', $parsing_method === 'imagemagick_identify')
      ->save();
    $status = \Drupal::service('image.toolkit.manager')->createInstance('imagemagick')->getExecManager()->checkPath('');
    if (!empty($status['errors'])) {
      // Bots running automated test on d.o. do not have binaries installed,
      // so the test will be skipped; it can be run locally where binaries are
      // installed.
      $this->markTestSkipped("Tests for '{$binaries}' cannot run because the binaries are not available on the shell path.");
    }

    // A list of files that will be tested.
    $files = [
      'public://image-test.png' => [
        'width' => 40,
        'height' => 20,
        'frames' => 1,
        'mimetype' => 'image/png',
        'colorspace' => 'SRGB',
      ],
      'public://image-test.gif' => [
        'width' => 40,
        'height' => 20,
        'frames' => 1,
        'mimetype' => 'image/gif',
        'colorspace' => 'SRGB',
      ],
      'dummy-remote://image-test.jpg' => [
        'width' => 40,
        'height' => 20,
        'frames' => 1,
        'mimetype' => 'image/jpeg',
        'colorspace' => 'SRGB',
      ],
      'public://test-multi-frame.gif' => [
        'skip_dimensions_check' => TRUE,
        'frames' => 13,
        'mimetype' => 'image/gif',
        'colorspace' => 'SRGB',
      ],
    ];

    // Setup a list of tests to perform on each type.
    $operations = [
      'resize' => [
        'function' => 'resize',
        'arguments' => ['width' => 20, 'height' => 10],
        'width' => 20,
        'height' => 10,
      ],
      'scale_x' => [
        'function' => 'scale',
        'arguments' => ['width' => 20],
        'width' => 20,
        'height' => 10,
      ],
      // Fuchsia background.
      'rotate_5' => [
        'function' => 'rotate',
        'arguments' => ['degrees' => 5, 'background' => '#FF00FF'],
        'width' => 41,
        'height' => 23,
      ],
      'convert_jpg' => [
        'function' => 'convert',
        'width' => 40,
        'height' => 20,
        'arguments' => ['extension' => 'jpeg'],
        'mimetype' => 'image/jpeg',
      ],
    ];

    // The file metadata manager service.
    $fmdm = $this->container->get('file_metadata_manager');

    // Prepare a copy of test files.
    $this->getTestFiles('image');
    file_unmanaged_copy(drupal_get_path('module', 'imagemagick') . '/misc/test-multi-frame.gif', 'public://', FILE_EXISTS_REPLACE);

    // Perform tests without caching.
    $config_mdm->set('metadata_cache.enabled', FALSE)->save();
    foreach ($files as $source_uri => $source_image_data) {
      $this->assertFalse($fmdm->has($source_uri));
      $source_image_md = $fmdm->uri($source_uri);
      $this->assertTrue($fmdm->has($source_uri));
      $first = TRUE;
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up a fresh image.
        if ($first) {
          $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        $source_image = $this->imageFactory->get($source_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        // In some cases the metadata can be generated on save without need to
        // re-read it back from the image.
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $source_image->getToolkit()->getFrames() === 1
        ) {
          $this->assertTrue($fmdm->has($saved_uri));
        }
        else {
          $this->assertFalse($fmdm->has($saved_uri));
        }

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        // In some cases the metadata can be generated on save without need to
        // re-read it back from the image.
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $saved_image->getToolkit()->getFrames() === 1 &&
          !($values['function'] === 'convert' && $source_image_data['frames'] > 1)
        ) {
          $this->assertIdentical(FileMetadataInterface::LOADED_BY_CODE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from file.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        switch ($parsing_method) {
          case 'imagemagick_identify':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
            }
            break;

          case 'getimagesize':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 1));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 0));
            }
            break;

        }
        $fmdm->release($saved_uri);

        $first = FALSE;
      }
      $fmdm->release($source_uri);
      $this->assertFalse($fmdm->has($source_uri));
    }

    // Perform tests with caching.
    $config_mdm->set('metadata_cache.enabled', TRUE)->save();
    foreach ($files as $source_uri => $source_image_data) {
      $first = TRUE;
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up a fresh image.
        $this->assertFalse($fmdm->has($source_uri));
        $source_image_md = $fmdm->uri($source_uri);
        $this->assertTrue($fmdm->has($source_uri));
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        $source_image = $this->imageFactory->get($source_uri);
        if ($first) {
          // First time load, metadata loaded from file.
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          // Further loads, metadata loaded from cache.
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $source_image->getToolkit()->getFrames() === 1
        ) {
          $this->assertTrue($fmdm->has($saved_uri));
        }
        else {
          $this->assertFalse($fmdm->has($saved_uri));
        }

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $saved_image->getToolkit()->getFrames() === 1 &&
          !($values['function'] === 'convert' && $source_image_data['frames'] > 1)
        ) {
          $this->assertIdentical(FileMetadataInterface::LOADED_BY_CODE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service. Should be cached.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from cache.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $saved_image_md->isMetadataLoaded($parsing_method));
        switch ($parsing_method) {
          case 'imagemagick_identify':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
            }
            break;

          case 'getimagesize':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 1));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 0));
            }
            break;

        }
        $fmdm->release($saved_uri);

        // We release the source image FileMetadata at each cycle to ensure
        // that metadata is read from cache.
        $fmdm->release($source_uri);
        $this->assertFalse($fmdm->has($source_uri));

        $first = FALSE;
      }
    }

    // Open source images again after deleting the temp folder files.
    // Source image data should now be cached, but temp files non existing.
    // Therefore we test that the toolkit can create a new temp file copy.
    // Note: on Windows, temp imagemagick file names have a
    // imaNNN.tmp.[image_extension] pattern so we cannot scan for
    // 'imagemagick'.
    $directory_scan = file_scan_directory('temporary://', '/ima.*/');
    $this->assertGreaterThan(0, count($directory_scan));
    foreach ($directory_scan as $file) {
      file_unmanaged_delete($file->uri);
    }
    $directory_scan = file_scan_directory('temporary://', '/ima.*/');
    $this->assertEquals(0, count($directory_scan));
    foreach ($files as $source_uri => $source_image_data) {
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up the source image. Parsing should be fully cached now.
        $fmdm->release($source_uri);
        $source_image_md = $fmdm->uri($source_uri);
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        $source_image = $this->imageFactory->get($source_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $source_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $source_image->getToolkit()->getFrames() === 1
        ) {
          $this->assertTrue($fmdm->has($saved_uri));
        }
        else {
          $this->assertFalse($fmdm->has($saved_uri));
        }

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $saved_image->getToolkit()->getFrames() === 1 &&
          !($values['function'] === 'convert' && $source_image_data['frames'] > 1)
        ) {
          $this->assertIdentical(FileMetadataInterface::LOADED_BY_CODE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service. Should be cached.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from cache.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_CACHE, $saved_image_md->isMetadataLoaded($parsing_method));
        switch ($parsing_method) {
          case 'imagemagick_identify':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
            }
            break;

          case 'getimagesize':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 1));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 0));
            }
            break;

        }
        $fmdm->release($saved_uri);
      }
      $fmdm->release($source_uri);
      $this->assertFalse($fmdm->has($source_uri));
    }

    // Files in temporary:// must not be cached.
    if ($parsing_method === 'imagemagick_identify') {
      file_unmanaged_copy(drupal_get_path('module', 'imagemagick') . '/misc/test-multi-frame.gif', 'temporary://', FILE_EXISTS_REPLACE);
      $source_uri = 'temporary://test-multi-frame.gif';
      $fmdm->release($source_uri);
      $source_image_md = $fmdm->uri($source_uri);
      $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded('imagemagick_identify'));
      $source_image = $this->imageFactory->get($source_uri);
      $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded('imagemagick_identify'));
      $fmdm->release($source_uri);
      $source_image_md = $fmdm->uri($source_uri);
      $source_image = $this->imageFactory->get($source_uri);
      $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded('imagemagick_identify'));
    }

    // Invalidate cache, and open source images again. Now, all files should be
    // parsed again.
    Cache::InvalidateTags([
      'config:imagemagick.file_metadata_plugin.imagemagick_identify',
      'config:file_mdm.file_metadata_plugin.getimagesize',
    ]);
    // Disallow caching on the test results directory.
    $config_mdm->set('metadata_cache.disallowed_paths', ['public://imagetest/*'])->save();
    foreach ($files as $source_uri => $source_image_data) {
      $fmdm->release($source_uri);
    }
    foreach ($files as $source_uri => $source_image_data) {
      $this->assertFalse($fmdm->has($source_uri));
      $source_image_md = $fmdm->uri($source_uri);
      $this->assertTrue($fmdm->has($source_uri));
      $first = TRUE;
      file_unmanaged_delete_recursive($this->testDirectory);
      file_prepare_directory($this->testDirectory, FILE_CREATE_DIRECTORY);
      foreach ($operations as $op => $values) {
        // Load up a fresh image.
        if ($first) {
          $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $source_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        }
        $source_image = $this->imageFactory->get($source_uri);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $source_image_md->isMetadataLoaded($parsing_method));
        $this->assertIdentical($source_image_data['mimetype'], $source_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertIdentical($source_image_data['height'], $source_image->getHeight());
          $this->assertIdentical($source_image_data['width'], $source_image->getWidth());
        }

        // Perform our operation.
        $source_image->apply($values['function'], $values['arguments']);

        // Save image.
        $saved_uri = $this->testDirectory . '/' . $op . substr($source_uri, -4);
        $this->assertFalse($fmdm->has($saved_uri));
        $this->assertTrue($source_image->save($saved_uri));
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $source_image->getToolkit()->getFrames() === 1
        ) {
          $this->assertTrue($fmdm->has($saved_uri));
        }
        else {
          $this->assertFalse($fmdm->has($saved_uri));
        }

        // Reload saved image and check data.
        $saved_image_md = $fmdm->uri($saved_uri);
        $saved_image = $this->imageFactory->get($saved_uri);
        if ($binaries === 'imagemagick' &&
          $parsing_method === 'imagemagick_identify' &&
          $saved_image->getToolkit()->getFrames() === 1 &&
          !($values['function'] === 'convert' && $source_image_data['frames'] > 1)
        ) {
          $this->assertIdentical(FileMetadataInterface::LOADED_BY_CODE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        else {
          $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        }
        $this->assertIdentical($values['function'] === 'convert' ? $values['mimetype'] : $source_image_data['mimetype'], $saved_image->getMimeType());
        if ($binaries === 'imagemagick' && $parsing_method === 'imagemagick_identify') {
          $this->assertIdentical($source_image_data['colorspace'], $source_image->getToolkit()->getColorspace());
        }
        if (!isset($source_image_data['skip_dimensions_check'])) {
          $this->assertEqual($values['height'], $saved_image->getHeight());
          $this->assertEqual($values['width'], $saved_image->getWidth());
        }
        $fmdm->release($saved_uri);

        // Get metadata via the file_mdm service.
        $saved_image_md = $fmdm->uri($saved_uri);
        // Should not be available at this stage.
        $this->assertIdentical(FileMetadataInterface::NOT_LOADED, $saved_image_md->isMetadataLoaded($parsing_method));
        // Get metadata from file.
        $saved_image_md->getMetadata($parsing_method);
        $this->assertIdentical(FileMetadataInterface::LOADED_FROM_FILE, $saved_image_md->isMetadataLoaded($parsing_method));
        switch ($parsing_method) {
          case 'imagemagick_identify':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 'height'));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 'width'));
            }
            break;

          case 'getimagesize':
            if (!isset($source_image_data['skip_dimensions_check'])) {
              $this->assertEqual($values['height'], $saved_image_md->getMetadata($parsing_method, 1));
              $this->assertEqual($values['width'], $saved_image_md->getMetadata($parsing_method, 0));
            }
            break;

        }
        $fmdm->release($saved_uri);

        $first = FALSE;
      }
      $fmdm->release($source_uri);
      $this->assertFalse($fmdm->has($source_uri));
    }
  }

  /**
   * Tests getSourceLocalPath() for re-creating local path.
   */
  public function testSourceLocalPath() {
    $status = \Drupal::service('image.toolkit.manager')->createInstance('imagemagick')->getExecManager()->checkPath('');
    if (!empty($status['errors'])) {
      // Bots running automated test on d.o. do not have binaries installed,
      // so the test will be skipped; it can be run locally where binaries are
      // installed.
      $this->markTestSkipped("Tests for 'imagemagick' cannot run because the binaries are not available on the shell path.");
    }

    $config = \Drupal::configFactory()->getEditable('imagemagick.settings');
    $config_mdm = \Drupal::configFactory()->getEditable('file_mdm.settings');

    // The file metadata manager service.
    $fmdm = $this->container->get('file_metadata_manager');

    // The file that will be tested.
    $source_uri = 'public://image-test.png';

    // Prepare a copy of test files.
    $this->getTestFiles('image');

    // Enable metadata caching.
    $config_mdm->set('metadata_cache.enabled', TRUE)->save();

    // Parse with identify.
    $config->set('use_identify', TRUE)->save();

    // Load up the image.
    $image = $this->imageFactory->get($source_uri);
    $this->assertEqual($source_uri, $image->getToolkit()->getSource());
    $this->assertEqual(drupal_realpath($source_uri), $image->getToolkit()->arguments()->getSourceLocalPath());

    // Free up the URI from the file metadata manager to force reload from
    // cache. Simulates that next imageFactory->get is from another request.
    $fmdm->release($source_uri);

    // Re-load the image, ensureLocalSourcePath should return the local path.
    $image1 = $this->imageFactory->get($source_uri);
    $this->assertEqual($source_uri, $image1->getToolkit()->getSource());
    $this->assertEqual(drupal_realpath($source_uri), $image1->getToolkit()->ensureSourceLocalPath());
  }

}
