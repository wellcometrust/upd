<?php

namespace Drupal\mailchimp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mailchimp Webhook controller.
 */
class MailchimpWebhookController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function endpoint($hash) {
    $return = 0;

    if (!empty($_POST)) {
      $data = $_POST['data'];
      $type = $_POST['type'];
      switch ($type) {
        case 'unsubscribe':
        case 'profile':
        case 'cleaned':
          mailchimp_get_memberinfo($data['list_id'], $data['email'], TRUE);
          break;

        case 'upemail':
          mailchimp_cache_clear_member($data['list_id'], $data['old_email']);
          mailchimp_get_memberinfo($data['list_id'], $data['new_email'], TRUE);
          break;

        case 'campaign':
          mailchimp_cache_clear_list_activity($data['list_id']);
          mailchimp_cache_clear_campaign($data['id']);
          break;
      }

      // Allow other modules to act on a webhook.
      \Drupal::moduleHandler()->invokeAll('mailchimp_process_webhook', array($type, $data));

      // Log event.
      \Drupal::logger('mailchimp')->info('Webhook type {type} has been processed.', array(
        'type' => $type));

      $return = 1;
    }

    $response = new Response(
      $return,
      Response::HTTP_OK,
      ['content-type' => 'text/plain']
    );
    $response->send();
    exit();
  }

}
