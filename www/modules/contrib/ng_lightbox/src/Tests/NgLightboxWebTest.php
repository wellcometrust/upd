<?php

namespace Drupal\ng_lightbox\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * A web test for NG Lightbox.
 *
 * @group ng_lightbox
 */
class NgLightboxWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['ng_lightbox', 'views', 'node', 'filter'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $config = \Drupal::configFactory()->getEditable('ng_lightbox.settings');
    $this->createContentType(['type' => 'page']);
    $node = $this->drupalCreateNode();
    $config->set('patterns', '/node/' . $node->id());
    $config->save();
  }

  /**
   * Test that we can render a modal even before selecting one from the admin.
   */
  public function testDefaultModal() {
    $this->drupalGet('/node');
    $this->assertRaw('data-dialog-type="modal"');
  }

}
