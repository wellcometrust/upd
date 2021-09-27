<?php

namespace Drupal\Tests\mailchimp_campaign\Functional;

/**
 * Tests core campaign functionality.
 *
 * @group mailchimp
 */
class MailchimpCampaignTest extends MailchimpCampaignTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['mailchimp', 'mailchimp_campaign', 'mailchimp_test'];

  /**
   * Tests retrieval of a specific campaign.
   */
  public function testGetCampaign() {
    $campaign_id = '42694e9e57';

    $campaign = mailchimp_get_campaign_data($campaign_id);

    $this->assertTrue(is_object($campaign), 'Tested retrieval of campaign data.');

    $this->assertSame($campaign->id, $campaign_id);
    $this->assertSame($campaign->type, 'regular');
    $this->assertSame($campaign->recipients->list_id, '57afe96172');
    $this->assertSame($campaign->settings->subject_line, 'Test Campaign');
    $this->assertTrue($campaign->tracking->html_clicks);
    $this->assertFalse($campaign->tracking->text_clicks);
  }

}
