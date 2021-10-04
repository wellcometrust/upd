<?php

namespace Drupal\mailchimp_lists\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure settings for a Mailchimp list webhook.
 */
class MailchimpListsWebhookSettingsForm extends ConfigFormBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a MailchimpListsWebhookSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger) {
    parent::__construct($config_factory);

    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_lists_webhook_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailchimp_lists.webhook'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $list_id = $this->getRequest()->attributes->get('_raw_variables')->get('list_id');

    $list = mailchimp_get_list($list_id);

    $form_state->set('list', $list);

    $default_webhook_events = mailchimp_lists_default_webhook_events();
    $enabled_webhook_events = mailchimp_lists_enabled_webhook_events($list_id);

    $form['webhook_events'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Enabled webhook events for the @name audience',
        [
          '@name' => $list->name,
        ]),
      '#tree' => TRUE,
    ];

    foreach ($default_webhook_events as $event => $name) {
      $form['webhook_events'][$event] = [
        '#type' => 'checkbox',
        '#title' => $name,
        '#default_value' => in_array($event, $enabled_webhook_events),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* @var \Mailchimp\MailchimpLists $mc_lists */
    $mc_lists = mailchimp_get_api_object('MailchimpLists');
    $list = $form_state->get('list');

    $webhook_events = $form_state->getValue('webhook_events');

    $events = [];
    foreach ($webhook_events as $webhook_id => $enable) {
      $events[$webhook_id] = ($enable === 1);
    }

    $result = FALSE;

    if (count($events) > 0) {
      $webhook_url = mailchimp_webhook_url();

      $webhooks = mailchimp_webhook_get($list->id);

      if (!empty($webhooks)) {
        foreach ($webhooks as $webhook) {
          if ($webhook->url == $webhook_url) {
            // Delete current webhook.
            mailchimp_webhook_delete($list->id, mailchimp_webhook_url());
          }
        }
      }

      $sources = [
        'user' => TRUE,
        'admin' => TRUE,
        'api' => FALSE,
      ];

      // Add webhook with enabled events.
      $result = mailchimp_webhook_add(
        $list->id,
        mailchimp_webhook_url(),
        $events,
        $sources
      );
    }

    if ($result) {
      $this->messenger->addStatus($this->t('Webhooks for audience "%name" have been updated.',
        [
          '%name' => $list->name,
        ]
      ));
    }
    else {
      $this->messenger->addWarning($this->t('Unable to update webhooks for audience "%name".',
        [
          '%name' => $list->name,
        ]
      ));
    }

    $form_state->setRedirect('mailchimp_lists.overview');
  }

}
