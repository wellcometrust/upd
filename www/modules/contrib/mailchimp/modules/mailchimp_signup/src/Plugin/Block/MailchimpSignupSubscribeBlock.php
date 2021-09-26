<?php

namespace Drupal\mailchimp_signup\Plugin\Block;

use Drupal\mailchimp_signup\Form\MailchimpSignupPageForm;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\mailchimp_signup\Entity\MailchimpSignup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Subscribe' block.
 *
 * @Block(
 *   id = "mailchimp_signup_subscribe_block",
 *   admin_label = @Translation("Subscribe Block"),
 *   category = @Translation("Mailchimp Signup"),
 *   module = "mailchimp_signup",
 *   deriver = "Drupal\mailchimp_signup\Plugin\Derivative\MailchimpSignupSubscribeBlock"
 * )
 */
class MailchimpSignupSubscribeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * A static counter used to generate the form_id.
   *
   * @var int
   */
  private static $counter = 0;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Form Builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new MailchimpSignupSubscribeBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = $messenger;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $signup_id = $this->getDerivativeId();

    /* @var $signup \Drupal\mailchimp_signup\Entity\MailchimpSignup */
    $signup = mailchimp_signup_load($signup_id);

    $form = new MailchimpSignupPageForm($this->messenger);

    $form->setFormID($this->getFormId($signup));
    $form->setSignup($signup);

    $content = $this->formBuilder->getForm($form);

    return $content;
  }

  /**
   * Get the ID of the form.
   *
   * @param \Drupal\mailchimp_signup\Entity\MailchimpSignup $entity
   *   An instance of the SignUp entity.
   *
   * @return string
   *   Returns the id of the form.
   */
  protected function getFormId(MailchimpSignup $entity) {
    // The base form_id.
    // We keep it the same way as it was until now,
    // without having to add the suffix. We are doing this
    // in case there are already existing form hooks relying
    // on this name, so that we cant at least keep some BC
    // before having to add the suffix for each form coming up next.
    $id = 'mailchimp_signup_subscribe_block_' . $entity->id . '_form';

    // Add the suffix in case we've already created one block
    // with a signup form.
    if (static::$counter && static::$counter >= 1) {
      $id = sprintf('%s_%d', $id, static::$counter);
    }

    static::$counter++;

    return $id;
  }

}
