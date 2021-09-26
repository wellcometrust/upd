<?php

namespace Drupal\Tests\mailchimp_campaign\Functional;

use Drupal\mailchimp_campaign_test\MailchimpCampaignConfigOverrider;
use Drupal\Tests\BrowserTestBase;

/**
 * Sets up Mailchimp Campaign module tests.
 */
abstract class MailchimpCampaignTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    \Drupal::configFactory()->addOverride(new MailchimpCampaignConfigOverrider());
  }

}
