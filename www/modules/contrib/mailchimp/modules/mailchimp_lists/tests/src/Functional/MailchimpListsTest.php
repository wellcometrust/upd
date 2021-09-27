<?php

namespace Drupal\Tests\mailchimp_lists\Functional;

/**
 * Tests core lists functionality.
 *
 * @group mailchimp
 */
class MailchimpListsTest extends MailchimpListsTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['mailchimp', 'mailchimp_lists', 'mailchimp_test'];

  /**
   * Tests that a list can be loaded.
   */
  public function testGetList() {
    $list_id = '57afe96172';

    $list = mailchimp_get_list($list_id);

    $this->assertSame($list->id, $list_id);
    $this->assertSame($list->name, 'Test List One');
  }

  /**
   * Tests retrieval of a specific set of lists.
   */
  public function testMultiListRetrieval() {
    $list_ids = [
      '57afe96172',
      'f4b7b26b2e',
    ];

    $lists = mailchimp_get_lists($list_ids);

    $this->assertSame(count($lists), 2, 'Tested correct list count on retrieval.');

    $this->assertSame($lists[$list_ids[0]]->id, $list_ids[0]);
    $this->assertSame($lists[$list_ids[0]]->name, 'Test List One');

    $this->assertSame($lists[$list_ids[1]]->id, $list_ids[1]);
    $this->assertSame($lists[$list_ids[1]]->name, 'Test List Two');
  }

  /**
   * Tests retrieval of mergevars for a set of lists.
   */
  public function testGetMergevars() {
    $list_ids = [
      '57afe96172',
    ];

    $mergevars = mailchimp_get_mergevars($list_ids);

    $this->assertCount(3, $mergevars[$list_ids[0]], 'Tested correct mergevar count on retrieval.');

    $this->assertSame($mergevars[$list_ids[0]][0]->tag, 'EMAIL');
    $this->assertSame($mergevars[$list_ids[0]][1]->tag, 'FNAME');
    $this->assertSame($mergevars[$list_ids[0]][2]->tag, 'LNAME');
  }

}
