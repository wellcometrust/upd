<?php

namespace Drupal\Tests\ng_lightbox\Kernel;

use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test the NG Lightbox service.
 *
 * @group ng_lightbox
 */
class NgLightboxTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  static $modules = ['system', 'ng_lightbox'];

  /**
   * Test a URL that only has a hash.
   */
  public function testHashOnlyUrls() {
    $url = Url::fromUserInput('#hash-only-url');
    $this->assertFalse($this->container->get('ng_lightbox')->isNgLightboxEnabledPath($url));
  }

}
