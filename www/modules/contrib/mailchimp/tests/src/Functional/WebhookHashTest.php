<?php

namespace Drupal\Tests\mailchimp\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Mailchimp webhook protection.
 *
 * @group mailchimp
 */
class WebhookHashTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['mailchimp'];


  /**
   * Tests configuring a text editor for an existing text format.
   */
  public function testWebhook() {
    // If there are no settings, any request should work.
    $this->drupalGet('mailchimp/webhook/foo');
    $this->assertSession()->statusCodeEquals(200);
    // If a hash is set, an invalid hash should fail.
    $this->config('mailchimp.settings')->set('webhook_hash', 'bar')->save();
    $this->drupalGet('mailchimp/webhook/foo');
    $this->assertSession()->statusCodeEquals(403);
    // If a hash is set and it matches, the request should work.
    $this->drupalGet('mailchimp/webhook/bar');
    $this->assertSession()->statusCodeEquals(200);
  }

}
