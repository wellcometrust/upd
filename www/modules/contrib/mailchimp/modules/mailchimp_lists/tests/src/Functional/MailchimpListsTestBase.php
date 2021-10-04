<?php

namespace Drupal\Tests\mailchimp_lists\Functional;

use Drupal\mailchimp_lists_test\MailchimpListsConfigOverrider;
use Drupal\Tests\BrowserTestBase;

/**
 * Sets up Mailchimp Lists/Audiences module tests.
 */
abstract class MailchimpListsTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    \Drupal::configFactory()->addOverride(new MailchimpListsConfigOverrider());
  }

}
