<?php

namespace Drupal\jquery_colorpicker\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Formatter class for jquery_colorpicker field.
 *
 * @FieldFormatter(
 *   id = "jquery_colorpicker_raw_rgb_display",
 *   label = @Translation("Raw RGB"),
 *
 *   field_types = {
 *      "jquery_colorpicker"
 *   }
 * )
 */
class JQueryColorpickerRawRgbDisplayFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    $summary[] = t('Displays a rgb representation of the color, with no HTML wrappers nor the # prefix');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#markup' => $this->hexToRgb($item->value),
      ];
    }

    return $element;
  }

  /**
   * Helper function to convert hex to rgb.
   */
  private function hexToRgb($hex) {
    $hex = str_replace("#", "", $hex);

    if (strlen($hex) == 3) {
      $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
      $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
      $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    }
    else {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
    }

    $rgb = [$r, $g, $b];

    // Returns the rgb values separated by commas.
    return implode(",", $rgb);
  }

}
