<?php

namespace Drupal\Tests\mailchimp\Functional;

/**
 * Tests core API functionality.
 *
 * @group mailchimp
 */
class MailchimpAPITest extends FunctionalMailchimpTestBase {

  /**
   * Tests that the test API has been loaded.
   */
  function testAPI() {
    $mailchimp_api = mailchimp_get_api_object();

    $this->assertNotNull($mailchimp_api);

    $this->assertEqual(get_class($mailchimp_api), 'Mailchimp\Tests\Mailchimp');
  }

}
