<?php

namespace Drupal\mailchimp_lists\Plugin\Field\FieldFormatter;

use Drupal\mailchimp_lists\Form\MailchimpListsSubscribeForm;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'mailchimp_lists_field_subscribe' formatter.
 *
 * @FieldFormatter (
 *   id = "mailchimp_lists_field_subscribe",
 *   label = @Translation("Subscription Form"),
 *   field_types = {
 *     "mailchimp_lists_subscription"
 *   }
 * )
 */
class MailchimpListsFieldSubscribeFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The Form Builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Create an instance of MailchimpListsFieldSubscribeFormatter.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, FormBuilderInterface $form_builder) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'show_interest_groups' => FALSE,
    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings();

    $form['show_interest_groups'] = [
      '#title' => $this->t('Show Interest Groups'),
      '#type' => 'checkbox',
      '#description' => $field_settings['show_interest_groups'] ? $this->t('Check to display interest group membership details.') : $this->t('To display Interest Groups, first enable them in the field instance settings.'),
      '#default_value' => $field_settings['show_interest_groups'] && $settings['show_interest_groups'],
      '#disabled' => !$field_settings['show_interest_groups'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings();

    $summary = [];

    if ($field_settings['show_interest_groups'] && $settings['show_interest_groups']) {
      $summary[] = $this->t('Display Interest Groups');
    }
    else {
      $summary[] = $this->t('Hide Interest Groups');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /* @var $item \Drupal\mailchimp_lists\Plugin\Field\FieldType\MailchimpListsSubscription */
    foreach ($items as $delta => $item) {
      $form = new MailchimpListsSubscribeForm();

      $field_name = $item->getFieldDefinition()->getName();

      // Give each form a unqiue ID in case of mulitiple subscription forms.
      $field_form_id = 'mailchimp_lists_' . $field_name . '_form';
      $form->setFormID($field_form_id);
      $form->setFieldInstance($item);
      $form->setFieldFormatter($this);

      $elements[$delta] = $this->formBuilder->getForm($form);
    }

    return $elements;
  }

}
