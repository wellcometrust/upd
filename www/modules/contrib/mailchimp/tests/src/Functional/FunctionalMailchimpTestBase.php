<?php

namespace Drupal\Tests\mailchimp\Functional;

use Drupal\mailchimp_test\MailchimpConfigOverrider;
use Drupal\Tests\BrowserTestBase;

/**
 * Sets up Mailchimp module tests.
 */
abstract class FunctionalMailchimpTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * If Mailchimp config should be overridden.
   *
   * @var bool
   */
  protected static $override = TRUE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['mailchimp', 'mailchimp_test', 'block'];

  /**
   * A user that can administrate Mailchimp.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * An authenticated user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $lowUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('page_title_block');

    $this->lowUser = $this->drupalCreateUser();
    $this->adminUser = $this->drupalCreateUser(['administer mailchimp']);

    if ($this::$override) {
      \Drupal::configFactory()->addOverride(new MailchimpConfigOverrider());
    }
  }

}
