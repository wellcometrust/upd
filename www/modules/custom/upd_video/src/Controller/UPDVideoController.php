<?php

namespace Drupal\upd_video\Controller;

use Drupal\media\MediaInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * An example controller.
 */
class UPDVideoController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content(MediaInterface $media, Request $request) {

    // We want to render the media entity using the 'lightbox_mode' view mode.


    $entity_type = 'media';
    $view_mode = 'lightbox_mode';
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);


    $build = $view_builder->view($media, $view_mode);
    $output = render($build);

    $build = [
      '#theme' => 'upd_video_lightbox',
      '#embed' => $output,
      '#attributes' => [],
    ];

    return $build;
  }

}
