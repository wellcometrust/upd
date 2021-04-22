<?php

namespace Drupal\ng_lightbox\Tests;

use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test basic functionality of the lightbox.
 *
 * @group ng_lightbox
 */
class NgLightboxTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['system', 'node', 'user', 'ng_lightbox'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    \Drupal::service('router.builder')->rebuild();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['ng_lightbox']);

    // Create the node type.
    NodeType::create(['type' => 'page'])->save();
  }

  /**
   * Test the pattern matching for link paths.
   */
  public function testPatternMatching() {

    // Test the patterns are enabled on links as expected.
    $node = Node::create(['type' => 'page', 'title' => $this->randomString()]);
    $node->save();
    $value_patterns = $node->toUrl()->toString();
    $config = \Drupal::configFactory()->getEditable('ng_lightbox.settings')
              ->set('patterns', $value_patterns)
              ->save();
    $this->assertLightboxEnabled(Link::fromTextAndUrl('Normal Path', $node->toUrl()));

    // Create a second node and make sure it doesn't get lightboxed.
    $secondnode = Node::create(['type' => 'page', 'title' => $this->randomString()]);
    $secondnode->save();
    $this->assertLightboxNotEnabled(Link::fromTextAndUrl('Second Path', $secondnode->toUrl()));

    // @TODO, these were in D7 but in D8, I can't see how you can even generate
    // a link with such a format so maybe it isn't needed at all?
    // The uppercase path should still be matched for a lightbox.
    // $this->assertLightboxNotEnabled(\Drupal::l('Uppercase Path', 'NODE/1'));
    // $this->assertLightboxNotEnabled(\Drupal::l('Alaised Path', $alias));
    // $this->assertLightboxNotEnabled(\Drupal::l('Empty Path', ''));
  }

  /**
   * Asserts the lightbox was enabled for the generated link.
   *
   * @param string $link
   *   The rendered link.
   */
  protected function assertLightboxEnabled($link) {
    $this->assertStringContainsString('use-ajax', $link->toString());
    $this->assertStringContainsString('data-dialog-type', $link->toString());
  }

  /**
   * Asserts the lightbox was not enabled for the generated link.
   *
   * @param string $link
   *   The rendered link.
   */
  protected function assertLightboxNotEnabled($link) {
    $this->assertNotContains('use-ajax', $link->toRenderable());
    $this->assertNotContains('data-dialog-type', $link->toRenderable());
  }

}
