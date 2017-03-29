<?php
namespace Drupal\prevnext\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
class NextPreviousBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    //Get the created time of the current node
    $node = \Drupal::request()->attributes->get('node');
    $created_time = $node->getCreatedTime();
    $link = "";
    $link .= $this->generatePrevious($created_time);
    $link .= $this->generateNext($created_time);
    return array('#markup' => $link);
  }
  /**
   * Lookup the previous node, i.e. youngest node which is still older than the node
   * currently being viewed.
   *
   * @param  string $created_time A unix time stamp
   * @return string               an html link to the previous node
   */
  private function generatePrevious($created_time) {
    return $this->generateNextPrevious('prev', $created_time);
  }
  /**
   * Lookup the next node, i.e. oldest node which is still younger than the node
   * currently being viewed.
   *
   * @param  string $created_time A unix time stamp
   * @return string               an html link to the next node
   */
  private function generateNext($created_time) {
    return $this->generateNextPrevious('next', $created_time);
  }
  /**
   * Lookup the next or previous node
   *
   * @param  string $direction    either 'next' or 'previous'
   * @param  string $created_time a Unix time stamp
   * @return string               an html link to the next or previous node
   */
  private function generateNextPrevious($direction = 'next', $created_time) {
    if ($direction === 'next') {
      $comparison_opperator = '>';
      $sort = 'ASC';
      $display_text = t('Next Article');
    }
    elseif ($direction === 'prev') {
      $comparison_opperator = '<';
      $sort = 'DESC';
      $display_text = t('Previous Article');
    }
    //Lookup 1 node younger (or older) than the current node
    $query = \Drupal::entityQuery('node');
    $next = $query->condition('created', $created_time, $comparison_opperator)
      ->condition('type', 'case_study')
      ->sort('created', $sort)
      ->range(0, 1)
      ->execute();
    //If this is not the youngest (or oldest) node
    
    if (!empty($next) && is_array($next)) {

      $next = array_values($next);
      $next = $next[0];
      $next_url = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $next);
      $next_url = Url::fromUri("base:/".$next_url);

      return \Drupal::l($display_text, $next_url);
    }
  }
}