<?php

namespace Drupal\upd_featured_case_studies\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Classified settings.
 */
class UpdFeaturedCaseStudiesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'upd_featured_case_studies_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $state = \Drupal::state();
    for ($i = 1; $i <= 3; $i++) {
      $form['featured_case_study_' . $i] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#selection_settings' => [
          'target_bundles' => ['case_study'],
        ],
        '#default_value' => Node::load($state->get('featured_case_study_' . $i)),
        '#title' => 'Featured case study ' . $i,
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = \Drupal::state();
    for ($i = 1; $i <= 3; $i++) {
      $state->set('featured_case_study_' . $i, $form_state->getValue('featured_case_study_' . $i));
    }
    parent::submitForm($form, $form_state);
  }

}
