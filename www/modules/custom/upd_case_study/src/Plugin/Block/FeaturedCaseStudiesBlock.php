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
    if (count($_GET) > 1) {
      return $build;
    }
    $config = $this->getConfiguration();
    $view_mode = $config['view_mode'];
    $config = \Drupal::config('upd_featured_case_studies.settings');
    $entity_ids = [];
    for ($i = 1; $i <= 3; $i++) {
      $entity_ids[$i] = $config->get('featured_case_study' . $i);
      if ($entity_ids[$i] == NULL) {
        return $build;
      }
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
