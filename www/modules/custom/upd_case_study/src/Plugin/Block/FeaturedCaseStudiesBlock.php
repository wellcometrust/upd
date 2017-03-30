<?php

namespace Drupal\upd_case_study\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a block with 3 latest case studies.
 *
 * @Block(
 *   id = "featured_case_studies",
 *   admin_label = @Translation("Featured case studies"),
 * )
 */
class FeaturedCaseStudiesBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'view_mode' => 'teaser',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#cache'] = ['contexts' => ['url.path']];
    // Only show when no query.
    if (count($_GET)) {
      return $build;
    }
    $config = $this->getConfiguration();
    $view_mode = $config['view_mode'];
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'case_study');
    $query->condition('sticky', 1);
    $query->range(0, 3);
    $query->sort('created', 'DESC');
    $entity_ids = $query->execute();
    if (count($entity_ids) < 3) {
      return $build;
    }
    $nodes = entity_load_multiple('node', $entity_ids);
    foreach ($nodes as $node) {
      $build['#items'][] = node_view($node, 'teaser');
    }
    $build['#grid'] = 3;
    $build['#theme'] = 'upd_grid_list';
    $build['#prefix'] = '<header class="search-results__header">';
    $build['#suffix'] = '</header>';
    return $build;
  }

}
