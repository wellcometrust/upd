<?php

namespace Drupal\mailchimp_lists\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Batch update Mailchimp lists mergevars.
 */
class MailchimpListsUpdateMergevarsForm extends ConfirmFormBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * MailchimpListsUpdateMergevarsForm constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(Request $request, MessengerInterface $messenger) {
    $this->request = $request;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_lists_admin_update_mergevars';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailchimp_lists.update_mergevars'];
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Update mergevars on all entities?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('mailchimp_lists.fields');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This can overwrite values configured directly on your Mailchimp Account.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_type = $this->request->get('entity_type');
    $bundle = $this->request->get('bundle');
    $field_name = $this->request->get('field_name');

    mailchimp_lists_update_member_merge_values($entity_type, $bundle, $field_name);

    $this->messenger->addStatus($this->t('Mergevars updated.'));
  }

}
