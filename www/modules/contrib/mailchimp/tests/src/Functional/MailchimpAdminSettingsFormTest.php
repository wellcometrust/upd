<?php

namespace Drupal\Tests\mailchimp\Functional;

/**
 * Tests the Mailchimp settings form.
 *
 * @group mailchimp
 */
class MailchimpAdminSettingsFormTest extends FunctionalMailchimpTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $override = FALSE;

  /**
   * Tests the basic behavior of the settings form.
   */
  public function testSettingsForm() {
    $this->drupalLogin($this->lowUser);
    $this->drupalGet('/admin/config/services/mailchimp');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/config/services/mailchimp');
    $this->assertSession()->statusCodeEquals(200);
    $this->submitForm([
      'api_key' => 'TEST_KEY',
    ], 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved');
    $this->assertSame('TEST_KEY', \Drupal::config('mailchimp.settings')->get('api_key'));
  }

}
