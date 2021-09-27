<?php

namespace Drupal\mailchimp_signup\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the MailchimpSignup entity delete form.
 *
 * @ingroup mailchimp_signup
 */
class MailchimpSignupDeleteForm extends EntityConfirmFormBase {

  /**
   * The router builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * MailchimpSignupDeleteForm constructor.
   *
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(RouteBuilderInterface $router_builder, MessengerInterface $messenger) {
    $this->routerBuilder = $router_builder;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.builder'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('mailchimp_signup.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    $this->routerBuilder->setRebuildNeeded();

    $this->messenger->addStatus($this->t('Signup Form %label has been deleted.', ['%label' => $this->entity->label()]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
