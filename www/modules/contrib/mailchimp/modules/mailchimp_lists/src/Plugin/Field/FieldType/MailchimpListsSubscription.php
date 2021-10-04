<?php

namespace Drupal\mailchimp_lists\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'mailchimp_lists_subscription' field type.
 *
 * @FieldType (
 *   id = "mailchimp_lists_subscription",
 *   label = @Translation("Mailchimp Subscription"),
 *   description = @Translation("Allows an entity to be subscribed to a Mailchimp audience."),
 *   default_widget = "mailchimp_lists_select",
 *   default_formatter = "mailchimp_lists_subscribe_default"
 * )
 */
class MailchimpListsSubscription extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'mc_list_id' => '',
      'double_opt_in' => 0,
      'send_welcome' => 0,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'subscribe_checkbox_label' => 'Subscribe',
      'show_interest_groups' => 0,
      'hide_subscribe_checkbox' => 0,
      'interest_groups_hidden' => 0,
      'interest_groups_label' => '',
      'merge_fields' => [],
      'unsubscribe_on_delete' => 0,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $columns = [
      'subscribe' => [
        'type' => 'int',
        'size' => 'tiny',
        'not null' => TRUE,
        'default' => 0,
      ],
      'interest_groups' => [
        'type' => 'text',
        'size' => 'normal',
        'not null' => TRUE,
        'serialize' => TRUE,
      ],
    ];
    return [
      'columns' => $columns,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['subscribe'] = DataDefinition::create('boolean')
      ->setLabel(t('Subscribe'))
      ->setDescription(t('True when an entity is subscribed to a audience.'));

    $properties['interest_groups'] = DataDefinition::create('any')
      ->setLabel(t('Interest groups'))
      ->setDescription(t('Interest groups selected for a audience.'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = parent::storageSettingsForm($form, $form_state, $has_data);

    $lists = mailchimp_get_lists();
    $options = ['' => $this->t('-- Select --')];
    foreach ($lists as $mc_list) {
      $options[$mc_list->id] = $mc_list->name;
    }

    $field_map = \Drupal::service('entity_field.manager')->getFieldMap();

    $field_definitions = [];
    foreach ($field_map as $entity_type => $fields) {
      $field_definitions[$entity_type] = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type);
    }

    // Prevent Mailchimp lists/audiences that have already been assigned to a
    // field appearing as field options.
    foreach ($field_map as $entity_type => $fields) {
      foreach ($fields as $field_name => $field_properties) {
        if ($field_properties['type'] == 'mailchimp_lists_subscription') {
          /* @var $field \Drupal\field\Entity\FieldStorageConfig */
          $field = $field_definitions[$entity_type][$field_name];
          $field_settings = $field->getSettings();

          if (($field_name != $this->getFieldDefinition()->getName()) && isset($field_settings['mc_list_id'])) {
            unset($options[$field_settings['mc_list_id']]);
          }
        }
      }
    }

    $refresh_lists_url = Url::fromRoute('mailchimp_lists.refresh');
    $mailchimp_url = Url::fromUri('https://admin.mailchimp.com', ['attributes' => ['target' => '_blank']]);

    $element['mc_list_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Mailchimp Audience'),
      '#multiple' => FALSE,
      '#description' => $this->t('Available Mailchimp audiences which are not already
        attached to Mailchimp Subscription Fields. If there are no options,
        make sure you have created an audience at @Mailchimp first, then @cacheclear.',
        [
          '@Mailchimp' => Link::fromTextAndUrl('Mailchimp', $mailchimp_url)->toString(),
          '@cacheclear' => Link::fromTextAndUrl('clear your audience cache', $refresh_lists_url)->toString(),
        ]),
      '#options' => $options,
      '#default_value' => $this->getSetting('mc_list_id'),
      '#required' => TRUE,
      '#disabled' => $has_data,
    ];
    $element['double_opt_in'] = [
      '#type' => 'checkbox',
      '#title' => 'Require subscribers to Double Opt-in',
      '#description' => 'New subscribers will be sent a link with an email they must follow to confirm their subscription.',
      '#default_value' => $this->getSetting('double_opt_in'),
      '#disabled' => $has_data,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);
    $mc_list_id = $this->getFieldDefinition()->getSetting('mc_list_id');

    if (empty($mc_list_id)) {
      \Drupal::messenger()->addError($this->t('Select an audience to sync with on the Field Settings tab before configuring the field instance.'));
      return $element;
    }
    $this->definition;
    $instance_settings = $this->definition->getSettings();

    $element['subscribe_checkbox_label'] = [
      '#title' => 'Subscribe Checkbox Label',
      '#type' => 'textfield',
      '#default_value' => isset($instance_settings['subscribe_checkbox_label']) ? $instance_settings['subscribe_checkbox_label'] : 'Subscribe',
    ];
    $element['show_interest_groups'] = [
      '#title' => "Enable Interest Groups",
      '#type' => "checkbox",
      '#default_value' => $instance_settings['show_interest_groups'],
    ];
    $element['hide_subscribe_checkbox'] = [
      '#title' => $this->t('Hide Subscribe Checkbox'),
      '#type' => 'checkbox',
      '#default_value' => $instance_settings['hide_subscribe_checkbox'],
      '#description' => $this->t('When Interest Groups are enabled, the "subscribe" checkbox is hidden and selecting any interest group will subscribe a user to the audience.'),
      '#states' => [
        'visible' => [
          'input[name="settings[show_interest_groups]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['interest_groups_hidden'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide Interest Groups.'),
      '#description' => $this->t('If checked, the Interest Groups will not be displayed, but the default values will be used.'),
      '#default_value' => $instance_settings['interest_groups_hidden'],
      '#states' => [
        'visible' => [
          'input[name="settings[show_interest_groups]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['interest_groups_label'] = [
      '#title' => "Interest Groups Label",
      '#type' => "textfield",
      '#default_value' => !empty($instance_settings['interest_groups_label']) ? $instance_settings['interest_groups_label'] : 'Interest Groups',
    ];
    $element['merge_fields'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Merge Fields'),
      '#description' => $this->t('Multi-value fields will only sync their first value to Mailchimp, as Mailchimp does not support multi-value fields.'),
      '#tree' => TRUE,
    ];

    $element['unsubscribe_on_delete'] = [
      '#title' => "Unsubscribe on deletion",
      '#type' => "checkbox",
      '#description' => $this->t('Unsubscribe entities from this audience when they are deleted.'),
      '#default_value' => $instance_settings['unsubscribe_on_delete'],
    ];

    $mv_defaults = $instance_settings['merge_fields'];
    $mergevars = mailchimp_get_mergevars([$mc_list_id]);

    $field_config = $this->getFieldDefinition();

    $fields = $this->getFieldmapOptions($field_config->get('entity_type'), $field_config->get('bundle'));
    $required_fields = $this->getFieldmapOptions($field_config->get('entity_type'), $field_config->get('bundle'), TRUE);

    // Prevent this subscription field appearing as a merge field option.
    $field_name = $this->getFieldDefinition()->getName();
    unset($fields[$field_name]);

    $fields_flat = OptGroup::flattenOptions($fields);

    foreach ($mergevars[$mc_list_id] as $mergevar) {
      $default_value = isset($mv_defaults[$mergevar->tag]) ? $mv_defaults[$mergevar->tag] : -1;
      $element['merge_fields'][$mergevar->tag] = [
        '#type' => 'select',
        '#title' => Html::escape($mergevar->name),
        '#default_value' => array_key_exists($default_value, $fields_flat) ? $default_value : '',
        '#required' => $mergevar->required,
      ];
      if (!$mergevar->required || $mergevar->tag === 'EMAIL') {
        $element['merge_fields'][$mergevar->tag]['#options'] = $fields;
        if ($mergevar->tag === 'EMAIL') {
          $element['merge_fields'][$mergevar->tag]['#description'] = $this->t('Any entity with an empty or invalid email address field value will simply be ignored by the Mailchimp subscription system. <em>This is why the Email field is the only required merge field which can sync to non-required fields.</em>');
        }
      }
      else {
        $element['merge_fields'][$mergevar->tag]['#options'] = $required_fields;
        $element['merge_fields'][$mergevar->tag]['#description'] = $this->t("Only 'required' and 'calculated' fields are allowed to be synced with Mailchimp 'required' merge fields.");
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->getValue();
    return (($value === NULL) || ($value === ''));
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
    parent::postSave($update);

    $choices = $this->values;

    // Only act if the field has a value to prevent unintended unsubscription.
    if (!empty($choices)) {
      $field_settings = $this->definition->getSettings();

      // Automatically subscribe if the field is configured to hide the
      // Subscribe checkbox and at least one interest group checkbox is checked.
      if ($field_settings['show_interest_groups'] && $field_settings['hide_subscribe_checkbox']) {
        if (!empty($choices['interest_groups'])) {
          $subscribe_from_interest_groups = FALSE;
          foreach ($choices['interest_groups'] as $group_id => $interests) {
            foreach ($interests as $interest_id => $value) {
              if (!empty($value)) {
                $subscribe_from_interest_groups = TRUE;
                continue;
              }
            }
          }

          $choices['subscribe'] = $subscribe_from_interest_groups;
        }
      }

      mailchimp_lists_process_subscribe_form_choices($choices, $this, $this->getEntity());
    }
  }

  /**
   * Returns the field 'subscribe' value.
   *
   * @return bool
   *   The field 'subscribe' value.
   */
  public function getSubscribe() {
    if (isset($this->values['subscribe'])) {
      return ($this->values['subscribe'] == 1);
    }

    return NULL;
  }

  /**
   * Returns the field 'interest_groups' value.
   *
   * @return array
   *   The field 'interest_groups' value.
   */
  public function getInterestGroups() {
    if (isset($this->values['interest_groups'])) {
      return $this->values['interest_groups'];
    }

    return NULL;
  }

  /**
   * Get an array with all possible Drupal properties for a given entity type.
   *
   * @param string $entity_type
   *   Name of entity whose properties to list/audience.
   * @param string $entity_bundle
   *   Optional bundle to limit available properties.
   * @param bool $required
   *   Set to TRUE if properties are required.
   * @param string $prefix
   *   Optional prefix for option IDs in the options list/audience.
   * @param string $tree
   *   Optional name of the parent element if the options are part of a tree.
   *
   * @return array
   *   List of properties that can be used as an #options list/audience.
   */
  private function getFieldmapOptions($entity_type, $entity_bundle = NULL, $required = FALSE, $prefix = NULL, $tree = NULL) {
    $options = [];
    if (!$prefix) {
      $options[''] = $this->t('-- Select --');
    }

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $entity_bundle);

    foreach ($field_definitions as $field_name => $field_definition) {
      $keypath = $prefix ? $prefix . ':' . $field_name : $field_name;

      $label = $field_definition->getLabel();

      if ($field_definition->getSetting('target_type')) {
        $target_type = $field_definition->getSetting('target_type');
        $target_definition = \Drupal::entityTypeManager()->getDefinition($target_type);
        // We offer fields on related fieldable entities (useful for field
        // collections).
        // But we only offer 1 level of depth to avoid loops.
        if ($target_definition->entityClassImplements(FieldableEntityInterface::class) && !$prefix) {
          $handler_settings = $field_definition->getSetting('handler_settings');
          $bundle = NULL;
          if ($target_definition->hasKey('bundle')) {
            // @todo Support multiple target bundles?
            if (!empty($handler_settings['target_bundles']) && count($handler_settings['target_bundles']) == 1) {
              $bundle = reset($handler_settings['target_bundles']);
            }
          }
          else {
            $bundle = $target_type;
          }
          if ($bundle) {
            $options[(string) $label] = $this->getFieldmapOptions($field_definition->getSetting('target_type'), $bundle, $required, $keypath . ':entity', $label);
          }
        }
      }
      elseif (!$required || $field_definition->isRequired() || $field_definition->isComputed()) {

        // Get a list of non-computed property definitions.
        $property_definitions = $field_definition->getFieldStorageDefinition()->getPropertyDefinitions();
        $property_definitions = array_filter($property_definitions, function (DataDefinitionInterface $property_definition) {
          return !$property_definition->isComputed();
        });

        if (count($property_definitions) > 1) {
          foreach ($property_definitions as $property => $property_definition) {
            $options[(string) $label][$keypath . ':' . $property] = $property_definition->getLabel();
          }
        }
        else {
          $options[$keypath] = $label;
        }
      }
    }
    return $options;
  }

}
