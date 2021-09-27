<?php

namespace Drupal\mailchimp_signup\Controller;

use Drupal\mailchimp_signup\Form\MailchimpSignupPageForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mailchimp Signup controller.
 */
class MailchimpSignupController extends ControllerBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * MailchimpSignupController constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(MessengerInterface $messenger, FormBuilderInterface $form_builder) {
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('form_builder')
    );
  }

  /**
   * View a Mailchimp signup form as a page.
   *
   * @param string $signup_id
   *   The ID of the MailchimpSignup entity to view.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function page($signup_id) {
    $content = [];

    $signup = mailchimp_signup_load($signup_id);

    $form = new MailchimpSignupPageForm($this->messenger);

    $form_id = 'mailchimp_signup_subscribe_page_' . $signup->id . '_form';
    $form->setFormID($form_id);
    $form->setSignup($signup);

    $content = $this->formBuilder->getForm($form);

    return $content;
  }

}
