<?php

namespace Drupal\upd_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block with 3 latest case studies.
 *
 * @Block(
 *   id = "upd_search_form",
 *   admin_label = @Translation("Search form"),
 * )
 */
class SearchFormBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'form_action' => 'search',
      'form_label' => 'Search the entire Understanding Patient Data site',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['form_action'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form action'),
      '#default_value' => $config['form_action'],
    ];

    $form['form_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form label'),
      '#default_value' => $config['form_label'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('form_action', $form_state->getValue('form_action'));
    $this->setConfigurationValue('form_label', $form_state->getValue('form_label'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $build = [
      '#theme' => 'upd_search_form',
      '#search_terms' => '',
      '#action' => [
        '#plain_text' => $config['form_action'],
      ],
      '#label' => $this->t($config['form_label']),
    ];
    if (!empty($_GET['query'])) {
      $terms = urldecode($_GET['query']);
      $build['#search_terms'] = [
        '#plain_text' => $terms,
      ];
    }
    return $build;
  }

}
