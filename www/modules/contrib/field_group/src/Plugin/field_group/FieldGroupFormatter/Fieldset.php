<?php

namespace Drupal\field_group\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Plugin implementation of the 'fieldset' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "fieldset",
 *   label = @Translation("Fieldset"),
 *   description = @Translation("This fieldgroup renders the inner content in a fieldset with the title as legend."),
 *   supported_contexts = {
 *     "form",
 *     "view",
 *   }
 * )
 */
class Fieldset extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {

    $element += [
      '#type' => 'fieldset',
      '#title' => Html::escape($this->t($this->getLabel())),
      '#pre_render' => [],
      '#attributes' => [],
    ];

    if ($this->getSetting('description')) {
      $element += [
        '#description' => $this->getSetting('description'),
      ];

      // When a fieldset has a description, an id is required.
      if (!$this->getSetting('id')) {
        $element['#id'] = Html::getId($this->group->group_name);
      }

    }

    if ($this->getSetting('id')) {
      $element['#id'] = Html::getId($this->getSetting('id'));
    }

    $classes = $this->getClasses();
    if (!empty($classes)) {
      $element['#attributes'] += ['class' => $classes];
    }

    if ($this->getSetting('required_fields')) {
      $element['#attached']['library'][] = 'field_group/formatter.fieldset';
      $element['#attached']['library'][] = 'field_group/core';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {

    $form = parent::settingsForm();

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('description'),
      '#weight' => -4,
    ];

    if ($this->context == 'form') {
      $form['required_fields'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Mark group as required if it contains required fields.'),
        '#default_value' => $this->getSetting('required_fields'),
        '#weight' => 2,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = parent::settingsSummary();

    if ($this->getSetting('required_fields')) {
      $summary[] = $this->t('Mark as required');
    }

    if ($this->getSetting('description')) {
      $summary[] = $this->t('Description : @description',
        ['@description' => $this->getSetting('description')]
      );
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultContextSettings($context) {
    $defaults = [
      'description' => '',
    ] + parent::defaultSettings($context);

    if ($context == 'form') {
      $defaults['required_fields'] = 1;
    }

    return $defaults;
  }

}
