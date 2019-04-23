<?php

namespace Drupal\node_weight\Form;

use Drupal\node\Entity\NodeType;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Node Weight configuration form.
 */
class NodeWeightForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_weight_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('node_weight.settings');

    // Available node types.
    $node_types = NodeType::loadMultiple();
    $options = [];
    foreach ($node_types as $node_type) {
      $options[$node_type->id()] = $node_type->label();
    }

    // Node types checkboxes.
    $form['checked_node_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Available on'),
      '#description' => $this->t('The Node Types this weight is available on.'),
      '#default_value' => $config->get('node_weight.checked_node_types') ?: [],
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('node_weight.settings');
    $config->set('node_weight.checked_node_types', array_filter(array_values($form_state->getValue('checked_node_types'))));
    $config->save();

    foreach ($form_state->getValue('checked_node_types') as $node => $node_type) {
      if ($node_type) {
        node_weight_create_field_node_weight($node_type);
      }
      else {
        node_weight_delete_field_node_weight($node_type);
      }
    }
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'node_weight.settings',
    ];
  }

}
