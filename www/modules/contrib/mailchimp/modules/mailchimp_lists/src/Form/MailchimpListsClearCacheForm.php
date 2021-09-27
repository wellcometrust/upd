<?php

namespace Drupal\mailchimp_lists\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Clear Mailchimp lists cache.
 */
class MailchimpListsClearCacheForm extends ConfirmFormBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * MailchimpListsClearCacheForm constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_lists_admin_clear_cache';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailchimp_lists.clear_cache'];
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Reset Mailchimp Audience Cache');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('mailchimp_lists.overview');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Confirm clearing of Mailchimp audience cache.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    mailchimp_get_lists([], TRUE);
    $this->messenger->addStatus($this->t('Mailchimp audience cache cleared.'));
  }

}
