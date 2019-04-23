<?php

namespace Drupal\node_weight\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\node\Entity\NodeType;

/**
 * Node Weight order form.
 */
class NodeOrderForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_order_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $node_type = \Drupal::request()->attributes->get('node_type');

    if (!NodeType::load($node_type)) {
      $output['text'] = ['#plain_text' => t('Not found the content type @type.', ['@type' => $node_type])];
      return $output;
    }

    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $type_enabled = node_weight_node_type_enabled($node_type);
    if (!$type_enabled) {
      $form = [
        $form['text'] = [
          '#plain_text' => t('Node Weight is disabled for this content type.'),
          '#suffix' => '<br />',
        ],
        $form['actions'] = ['#type' => 'actions'],
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => t('Enable'),
          '#submit' => ['::submitFormEnableNodeWeight'],
        ],
      ];
    }
    else {

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $nids = \Drupal::entityQuery('node')
        ->condition('type', $node_type)
        ->condition('langcode', $language)
        ->sort('field_node_weight', 'ASC')
        ->sort('created', 'ASC')
        ->execute();

      $nodes = Node::loadMultiple($nids);

      $header = [
        'Label' => ['data' => t('Node Title')],
        'Enabled' => ['data' => t('Enabled')],
        '' => ['data' => ''],
        'Operations' => ['data' => t('Operations')],
      ];

      $form['ntable'] = [
        '#type' => 'table',
        '#header' => $header,
        '#empty' => t('There are no items yet.'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'ntable-order-weight',
          ],
        ],
      ];

      // Build the table rows and columns.
      foreach ($nodes as $id => $entity) {

        $weight = $entity->get('field_node_weight')->value ?: 0;

        // TableDrag: Mark the table row as draggable.
        $form['ntable'][$id]['#attributes']['class'][] = 'draggable';

        $form['ntable'][$id]['label'] = [
          '#type' => 'link',
          '#title' => $entity->label(),
          '#url' => Url::fromRoute('entity.node.canonical', ['node' => $id]),

        ];

        $form['ntable'][$id]['enabled'] = [
          '#type' => 'checkbox',
          '#value' => $entity->get('status')->value,
        ];

        // TableDrag: Weight column element.
        $form['ntable'][$id]['weight'] = [
          '#type' => 'weight',
          '#title' => t('Weight for @title', ['@title' => $entity->label()]),
          '#title_display' => 'invisible',
          '#default_value' => $weight,
          '#delta' => 100,
          // Classify the weight element for #tabledrag.
          '#attributes' => ['class' => ['ntable-order-weight']],
        ];

        // Operations (dropbutton) column.
        $form['ntable'][$id]['operations'] = [
          '#type' => 'operations',
          '#links' => [],
        ];
        $form['ntable'][$id]['operations']['#links']['edit'] = [
          'title' => t('Edit'),
          'url' => Url::fromRoute('entity.node.edit_form', ['node' => $id]),
        ];
        $form['ntable'][$id]['operations']['#links']['delete'] = [
          'title' => t('Delete'),
          'url' => Url::fromRoute('entity.node.delete_form', ['node' => $id]),
        ];
      }

      $form['actions'] = ['#type' => 'actions'];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Save changes'),
      ];

    }

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Edit Order submission handler.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach (Element::children($form_state->getUserInput()['ntable']) as $tablerow => $nid) {
      $new_weight = $form_state->getUserInput()['ntable'][$nid]['weight'];
      if ($new_weight !== $form['ntable'][$nid]['weight']['#default_value']) {
        node_weight_set_node_field_value($nid, 'field_node_weight', $new_weight);
      }

      $new_enabled = isset($form_state->getUserInput()['ntable'][$nid]['enabled']) ?: 0;
      if ($new_enabled !== $form['ntable'][$nid]['enabled']['#value']) {
        node_weight_set_node_field_value($nid, 'status', $new_enabled);
      }
    }
    return parent::submitForm($form, $form_state);
  }

  /**
   * Enable Node Weight for given content type submission hanlder.
   *
   * {@inheritdoc}
   */
  public function submitFormEnableNodeWeight(array &$form, FormStateInterface $form_state) {
    $node_type = \Drupal::request()->attributes->get('node_type');
    node_weight_add_node_type_to_config($node_type);
    node_weight_create_field_node_weight($node_type);

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
