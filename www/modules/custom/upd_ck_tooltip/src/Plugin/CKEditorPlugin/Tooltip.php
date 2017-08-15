<?php

namespace Drupal\udp_ck_tooltip\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\ckeditor\CKEditorPluginCssInterface;

/**
 * Defines the "tooltip" plugin.
 *
 * @CKEditorPlugin(
 *   id = "tooltip",
 *   label = @Translation("CKEditor tooltip"),
 *   module = "udp_ck_tooltip"
 * )
 */
class Tooltip extends CKEditorPluginBase implements CKEditorPluginCssInterface {

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getFile().
   */
  public function getFile() {
    return base_path() . 'libraries/ck_tooltip/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return array(
      'tooltip' => array(
        'label' => t('Tooltips'),
        'image' => base_path() . 'libraries/ck_tooltip/icons/tooltip.png',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    return array(
      base_path() . 'libraries/ck_tooltip/assets/editor_styles.css',
    );
  }

}
