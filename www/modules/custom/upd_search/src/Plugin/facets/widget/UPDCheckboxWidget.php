<?php

namespace Drupal\upd_search\Plugin\facets\widget;

use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;
/**
 * The checkbox / radios widget.
 *
 * @FacetsWidget(
 *   id = "updcheckbox",
 *   label = @Translation("UPD checkboxes"),
 *   description = @Translation("A configurable widget that shows a list of checkboxes"),
 * )
 */
class UPDCheckboxWidget extends CheckboxWidget {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);
    return $build;
  }

}
