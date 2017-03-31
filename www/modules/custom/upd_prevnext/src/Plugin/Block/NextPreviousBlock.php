<?php

namespace Drupal\upd_prevnext\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Next Previous' Block.
 *
 * @Block(
 * id = "nextpreviousblock",
 * admin_label = @Translation("Next Previous Block"),
 * )
 */
class NextPreviousBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the created time of the current node.
    $build = [];
    $build['wrapper_prefix']['#markup'] = '<aside><nav><ul class="content-pagination grid">';
    $node = \Drupal::request()->attributes->get('node');
    $created_time = $node->getCreatedTime();
    $previous_node = $this->generatePrevious($created_time);
    if ($previous_node != NULL) {
      $build['previous_prefix']['#markup'] = '<li class="content-pagination__item content-pagination__item--previous grid__item">';
      $build['previous_link_prefix']['#markup'] = '<a class="content-pagination__control" href="' . $previous_node['next_url'] . '">';
      $build['previous_link']['#markup'] = '<span class="content-pagination__description">' . $previous_node['display_text'] . '</span>';
      $build['previous_link_title']['#markup'] = '<span class="content-pagination__title">' . $previous_node['title'] . '</span>';
      $build['previous_link_decoration_prefix']['#markup'] = '<div class="content-pagination__decoration">';
      $build['previous_link_decoration_svg']['#markup'] = '<svg class="svg-icon" xmlns="http://www.w3.org/2000/svg" title="' . $previous_node['title'] . '">';
      $build['previous_link_decoration_title']['#markup'] = '<title>' . $previous_node['title'] . '</title><use xlink:href="#svg-icon-chevron-left" />';
      $build['previous_link_decoration_sufix']['#markup'] = '</svg></div>';
      $build['previous_link_suffix']['#markup'] = '</a>';
      $build['previous_sufix']['#markup'] = '</li>';
    }
    $next_node = $this->generateNext($created_time);
    if ($next_node != NULL) {
      $build['next_prefix']['#markup'] = '<li class="content-pagination__item content-pagination__item--next grid__item">';
      $build['next_link_prefix']['#markup'] = '<a class="content-pagination__control" href="' . $next_node['next_url'] . '">';
      $build['next_link']['#markup'] = '<span class="content-pagination__description">' . $next_node['display_text'] . '</span>';
      $build['next_link_title']['#markup'] = '<span class="content-pagination__title">' . $next_node['title'] . '</span>';
      $build['next_link_decoration_prefix']['#markup'] = '<div class="content-pagination__decoration">';
      $build['next_link_decoration_svg']['#markup'] = '<svg class="svg-icon" xmlns="http://www.w3.org/2000/svg" title="' . $next_node['title'] . '">';
      $build['next_link_decoration_title']['#markup'] = '<title>' . $next_node['title'] . '</title><use xlink:href="#svg-icon-chevron-right" />';
      $build['next_link_decoration_sufix']['#markup'] = '</svg></div>';
      $build['next_link_suffix']['#markup'] = '</a>';
      $build['next_sufix']['#markup'] = '</li>';
    }
    $build['wrapper_sufix']['#markup'] = '</aside></nav></ul>';
    return $build;
  }

  /**
   * Lookup the previous node, i.e. older node.
   *
   * @param string $created_time
   *   A unix time stamp.
   *
   * @return string
   *   an html link to the previous node
   */
  private function generatePrevious($created_time) {
    return $this->generateNextPrevious($created_time, 'prev');
  }

  /**
   * Lookup the next node, i.e. younger node.
   *
   * @param string $created_time
   *   A unix time stamp.
   *
   * @return string
   *   an html link to the next node
   */
  private function generateNext($created_time) {
    return $this->generateNextPrevious($created_time, 'next');
  }

  /**
   * Lookup the next or previous node.
   *
   * @param string $created_time
   *   A Unix time stamp.
   * @param string $direction
   *   Either 'next' or 'previous'.
   *
   * @return string
   *   an html link to the next or previous node
   */
  private function generateNextPrevious($created_time, $direction = 'next') {
    if ($direction === 'next') {
      $comparison_opperator = '>';
      $sort = 'ASC';
      $display_text = t('Next case study');
    }
    elseif ($direction === 'prev') {
      $comparison_opperator = '<';
      $sort = 'DESC';
      $display_text = t('Previous case study');
    }
    // Lookup 1 node younger (or older) than the current node.
    $query = \Drupal::entityQuery('node');
    $next = $query->condition('created', $created_time, $comparison_opperator)
      ->condition('type', 'case_study')
      ->sort('created', $sort)
      ->range(0, 1)
      ->execute();
    // If this is not the youngest (or oldest) node.
    if (!empty($next) && is_array($next)) {

      $next = array_values($next);
      $next = $next[0];
      $next_url = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $next);
      $next_node = [];
      $next_node['title'] = Node::load($next)->title->value;
      $next_node['display_text'] = $display_text;
      $next_node['next_url'] = $next_url;
      return $next_node;
    }
  }

}
