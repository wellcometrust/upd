<?php

namespace Drupal\Tests\mailchimp\Functional;

use Drupal\mailchimp_test\MailchimpConfigOverrider;
use Drupal\Tests\BrowserTestBase;

if (!class_exists('Mailchimp\Mailchimp')) {
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/Mailchimp.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpAPIException.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpAutomations.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpCampaigns.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpConnectedSites.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpEcommerce.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpLists.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpReports.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/MailchimpTemplates.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/http/MailchimpHttpClientInterface.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/http/MailchimpCurlHttpClient.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/http/MailchimpGuzzleHttpClient.php';
  include_once __DIR__ . '/../../../lib/mailchimp-api-php/src/Mailchimp.php';
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/Client.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/Mailchimp.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpAutomations.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpCampaigns.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpEcommerce.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpLists.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpReports.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpTemplates.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpTestHttpClient.php";
  include_once __DIR__ . "/../../../lib/mailchimp-api-php/tests/src/MailchimpTestHttpResponse.php";
}

/**
 * Sets up Mailchimp module tests.
 */
abstract class FunctionalMailchimpTestBase extends BrowserTestBase {

  /**
   * If Mailchimp config should be overridden.
   *
   * @var bool
   */
  protected static $override = TRUE;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['mailchimp', 'mailchimp_test', 'block'];

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
