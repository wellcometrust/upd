<?php

namespace Drupal\node_weight\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'weight selector' widget.
 *
 * @FieldWidget(
 *   id = "weight_selector",
 *   label = @Translation("Weight Selector"),
 *   field_types = {
 *     "weight",
 *     "integer"
 *   }
 * )
 */
class WeightSelectorWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Pull together info we need to build the element.
    $value = isset($items[$delta]->value) ? $items[$delta]->value : 0;
    $field_settings = $this->getFieldSettings();
    $range = range($field_settings['min'], $field_settings['max']);

    // Build the element render array.
    $element += [
      '#type' => 'select',
      '#options' => array_combine($range, $range),
      '#default_value' => $value,
    // '#empty_option' => '--',.
    ];

    return ['value' => $element];
  }

}
