<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick;

/**
 * Defines imagemagick Scale and crop operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagemagick_scale_and_crop",
 *   toolkit = "imagemagick",
 *   operation = "scale_and_crop",
 *   label = @Translation("Scale and crop"),
 *   description = @Translation("Scales an image to the exact width and height given. This plugin achieves the target aspect ratio by cropping the original image equally on both sides, or equally on the top and bottom. This function is useful to create uniform sized avatars from larger images.")
 * )
 */
class ScaleAndCrop extends ImagemagickImageToolkitOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'width' => [
        'description' => 'The target width, in pixels',
      ],
      'height' => [
        'description' => 'The target height, in pixels',
      ],
      'filter' => [
        'description' => 'An optional filter to apply for the resize',
        'required' => FALSE,
        'default' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    $actual_width = $this->getToolkit()->getWidth();
    $actual_height = $this->getToolkit()->getHeight();

    $scale_factor = max($arguments['width'] / $actual_width, $arguments['height'] / $actual_height);

    $arguments['x'] = (int) round(($actual_width * $scale_factor - $arguments['width']) / 2);
    $arguments['y'] = (int) round(($actual_height * $scale_factor - $arguments['height']) / 2);
    $arguments['resize'] = [
      'width' => (int) round($actual_width * $scale_factor),
      'height' => (int) round($actual_height * $scale_factor),
      'filter' => $arguments['filter'],
    ];

    // Fail when width or height are 0 or negative.
    if ($arguments['width'] <= 0) {
      throw new \InvalidArgumentException("Invalid width ('{$arguments['width']}') specified for the image 'scale_and_crop' operation");
    }
    if ($arguments['height'] <= 0) {
      throw new \InvalidArgumentException("Invalid height ('{$arguments['height']}') specified for the image 'scale_and_crop' operation");
    }

    return $arguments;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments = []) {
    return $this->getToolkit()->apply('resize', $arguments['resize'])
        && $this->getToolkit()->apply('crop', $arguments);
  }

}
