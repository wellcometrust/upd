<?php

namespace Drupal\mailchimp_lists\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Mailchimp Lists/Audiences controller.
 */
class MailchimpListsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function overview() {
    $content = [];

    $lists_admin_url = Url::fromUri('https://admin.mailchimp.com/lists/', ['attributes' => ['target' => '_blank']]);

    // phpcs:disable
    $lists_empty_message = $this->t('You don\'t have any audiences configured in your
      Mailchimp account, (or you haven\'t configured your API key correctly on
      the Global Settings tab). Head over to @link and create some audiences, then
      come back here and click "Refresh audiences from Mailchimp"',
      ['@link' => Link::fromTextAndUrl($this->t('Mailchimp'), $lists_admin_url)->toString()]);
    // phpcs:enable

    $content['lists_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Members'),
        $this->t('Webhook Status'),
      ],
      '#empty' => $lists_empty_message,
    ];

    $mc_lists = mailchimp_get_lists();
    $total_webhook_events = count(mailchimp_lists_default_webhook_events());

    foreach ($mc_lists as $mc_list) {
      $enabled_webhook_events = count(mailchimp_lists_enabled_webhook_events($mc_list->id));
      $webhook_url = Url::fromRoute('mailchimp_lists.webhook', ['list_id' => $mc_list->id]);
      $webhook_link = Link::fromTextAndUrl('update', $webhook_url);

      $webhook_status = $enabled_webhook_events . ' of ' . $total_webhook_events . ' enabled (' . $webhook_link->toString() . ')';

      $list_url = Url::fromUri('https://admin.mailchimp.com/lists/dashboard/overview?id=' . $mc_list->id, ['attributes' => ['target' => '_blank']]);

      $content['lists_table'][$mc_list->id]['name'] = [
        // phpcs:disable
        '#title' => $this->t($mc_list->name),
        // phpcs:enable
        '#type' => 'link',
        '#url' => $list_url,
      ];
      $content['lists_table'][$mc_list->id]['member_count'] = [
        '#markup' => $mc_list->stats->member_count,
      ];
      $content['lists_table'][$mc_list->id]['web_id'] = [
        '#markup' => $webhook_status,
      ];
    }

    $refresh_url = Url::fromRoute('mailchimp_lists.refresh', ['destination' => 'admin/config/services/mailchimp/lists']);

    $content['refresh_link'] = [
      '#title' => 'Refresh audiences from Mailchimp',
      '#type' => 'link',
      '#url' => $refresh_url,
      '#attributes' => [
        'class' => [
          'button',
          'button-action',
          'button--primary',
          'button--small',
        ],
      ],
    ];

    $mailchimp_lists_url = Url::fromUri('https://admin.mailchimp.com/lists', ['attributes' => ['target' => '_blank']]);

    $content['mailchimp_list_link'] = [
      '#title' => $this->t('Go to Mailchimp Audiences'),
      '#type' => 'link',
      '#url' => $mailchimp_lists_url,
    ];

    return $content;
  }

}
