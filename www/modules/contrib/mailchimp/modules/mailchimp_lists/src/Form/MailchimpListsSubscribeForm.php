<?php

namespace Drupal\mailchimp_lists\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimp_lists\Plugin\Field\FieldFormatter\MailchimpListsFieldSubscribeFormatter;
use Drupal\mailchimp_lists\Plugin\Field\FieldType\MailchimpListsSubscription;
use Drupal\Core\StringTranslation\StringTranslationTrait;
/**
 * Subscribe to a Mailchimp list/audience.
 */
class MailchimpListsSubscribeForm extends FormBase {

  use StringTranslationTrait;
  /**
   * The ID for this form.
   *
   * Set as class property so it can be overwritten as needed.
   *
   * @var string
   */
  private $formId = 'mailchimp_lists_subscribe';

  /**
   * The MailchimpListsSubscription field instance used to build this form.
   *
   * @var \Drupal\mailchimp_lists\Plugin\Field\FieldType\MailchimpListsSubscription
   */
  private $fieldInstance;

  /**
   * A reference to the field formatter used to build this form.
   *
   * Used to get field configuration.
   *
   * @var \Drupal\mailchimp_lists\Plugin\Field\FieldFormatter\MailchimpListsFieldSubscribeFormatter
   */
  private $fieldFormatter;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->formId;
  }

  /**
   * Sets the form ID.
   *
   * @param string $formId
   *   The form ID.
   */
  public function setFormId($formId) {
    $this->formId = $formId;
  }

  /**
   * Sets the field instance service.
   *
   * @param \Drupal\mailchimp_lists\Plugin\Field\FieldType\MailchimpListsSubscription $fieldInstance
   *   The field instance service.
   */
  public function setFieldInstance(MailchimpListsSubscription $fieldInstance) {
    $this->fieldInstance = $fieldInstance;
  }

  /**
   * Sets the field formatter service.
   *
   * @param \Drupal\mailchimp_lists\Plugin\Field\FieldFormatter\MailchimpListsFieldSubscribeFormatter $fieldFormatter
   *   The field formatter service.
   */
  public function setFieldFormatter(MailchimpListsFieldSubscribeFormatter $fieldFormatter) {
    $this->fieldFormatter = $fieldFormatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailchimp_lists.subscribe'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $field_settings = $this->fieldInstance->getFieldDefinition()->getSettings();
    $field_formatter_settings = $this->fieldFormatter->getSettings();

    $mc_list = mailchimp_get_list($field_settings['mc_list_id']);

    $email = mailchimp_lists_load_email($this->fieldInstance, $this->fieldInstance->getEntity());
    if (!$email) {
      return [];
    }

    $field_name = $this->fieldInstance->getFieldDefinition()->getName();

    // Determine if a user is subscribed to the list.
    $is_subscribed = mailchimp_is_subscribed($mc_list['id'], $email);
    $wrapper_key = 'mailchimp_' . $field_name;
    $form['wrapper_key'] = [
      '#type' => 'hidden',
      '#default_value' => $wrapper_key,
    ];
    $form[$wrapper_key] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#description' => $this->fieldInstance->getFieldDefinition()->getDescription(),
      '#attributes' => [
        'class' => [
          'mailchimp-newsletter-wrapper',
          'mailchimp-newsletter-' . $field_name,
        ],
      ],
    ];
    // Add title and description to lists for anonymous users or if requested:
    $form[$wrapper_key]['subscribe'] = [
      '#type' => 'checkbox',
      '#title' => 'Subscribe',
      '#disabled' => $this->fieldInstance->getFieldDefinition()->isRequired(),
      '#required' => $this->fieldInstance->getFieldDefinition()->isRequired(),
      '#default_value' => $this->fieldInstance->getFieldDefinition()->isRequired() || $is_subscribed,
    ];
    // Present interest groups:
    if ($field_settings['show_interest_groups'] && $field_formatter_settings['show_interest_groups']) {
      // Perform test in case error comes back from MCAPI when getting groups:
      if (is_array($mc_list['intgroups'])) {
        $form[$wrapper_key]['interest_groups'] = [
          '#type' => 'fieldset',
          '#title' => isset($field_settings['interest_groups_label']) ? $field_settings['interest_groups_label'] : $this->t('Interest Groups'),
          '#weight' => 100,
          '#states' => [
            'invisible' => [
              ':input[name="' . $wrapper_key . '[subscribe]"]' => ['checked' => FALSE],
            ],
          ],
        ];

        $groups_default = $this->fieldInstance->getInterestGroups();

        if ($groups_default == NULL) {
          $groups_default = [];
        }

        $form[$wrapper_key]['interest_groups'] += mailchimp_interest_groups_form_elements($mc_list, $groups_default, $email);
      }
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $wrapper_key = $form_state->getValue('wrapper_key');
    $choices = $form_state->getValue($wrapper_key);

    mailchimp_lists_process_subscribe_form_choices($choices, $this->fieldInstance, $this->fieldInstance->getEntity());
  }

}
