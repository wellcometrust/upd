<?php

namespace Drupal\mailchimp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mailchimp Webhook controller.
 */
class MailchimpWebhookController extends ControllerBase {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a MailchimpWebhookController.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, LoggerInterface $logger) {
    $this->moduleHandler = $moduleHandler;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('logger.factory')->get('mailchimp')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function endpoint($hash) {
    $return = 0;

    // Return early if the hash in the request does not match.
    $webhook_hash = $this->config('mailchimp.settings')->get('webhook_hash');
    if (!empty($webhook_hash) && $webhook_hash !== $hash) {
      $response = new Response(
        $return,
        Response::HTTP_FORBIDDEN,
        ['content-type' => 'text/plain']
      );
      return $response;
    }

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
      $this->moduleHandler->invokeAll('mailchimp_process_webhook', [$type, $data]);

      // Log event.
      $this->logger->info('Webhook type {type} has been processed.', [
        'type' => $type,
      ]);

      $return = 1;
    }

    $response = new Response(
      $return,
      Response::HTTP_OK,
      ['content-type' => 'text/plain']
    );
    return $response;
  }

}
