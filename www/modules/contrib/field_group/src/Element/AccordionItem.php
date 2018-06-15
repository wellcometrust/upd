<?php

namespace Drupal\field_group\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for an accordion item.
 *
 * @FormElement("field_group_accordion_item")
 */
class AccordionItem extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#open' => TRUE,
      '#theme_wrappers' => ['field_group_accordion_item'],
    ];
  }

}
