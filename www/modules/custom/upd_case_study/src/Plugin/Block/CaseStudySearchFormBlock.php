<?php

namespace Drupal\upd_case_study\Plugin\Block;

use Drupal\upd_search\Plugin\Block\SearchFormBlock;

/**
 * Provides a block with 3 latest case studies.
 *
 * @Block(
 *   id = "upd_case_study_search_form",
 *   admin_label = @Translation("Case study search form"),
 * )
 */
class CaseStudySearchFormBlock extends SearchFormBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = parent::build();
    $build['#prefix'] = '<div class="refine">';
    $build['#suffix'] = '</div>';
    return $build;
  }

}
