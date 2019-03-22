<?php

/**
 * @file
 * API documentation for the ImageMagick module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the settings before an image is parsed by the ImageMagick toolkit.
 *
 * ImageMagick does not support stream wrappers so this hook allows modules to
 * resolve URIs of image files to paths on the local filesystem.
 * Modules can also decide to move files from remote systems to the local
 * file system to allow processing.
 *
 * @param \Drupal\imagemagick\ImagemagickExecArguments $arguments
 *   The ImageMagick/GraphicsMagick execution arguments object.
 *
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::parseFile()
 * @see \Drupal\imagemagick\ImagemagickExecArguments::getSource()
 * @see \Drupal\imagemagick\ImagemagickExecArguments::setSourceLocalPath()
 * @see \Drupal\imagemagick\ImagemagickExecArguments::getSourceLocalPath()
 */
function hook_imagemagick_pre_parse_file_alter(\Drupal\imagemagick\ImagemagickExecArguments $arguments) {
}

/**
 * Alter an image after it has been converted by the ImageMagick toolkit.
 *
 * ImageMagick does not support remote file systems, so modules can decide
 * to move temporary files from the local file system to remote destination
 * systems.
 *
 * @param \Drupal\imagemagick\ImagemagickExecArguments $arguments
 *   The ImageMagick/GraphicsMagick execution arguments object.
 *
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::save()
 * @see \Drupal\imagemagick\ImagemagickExecArguments::getDestination()
 * @see \Drupal\imagemagick\ImagemagickExecArguments::getDestinationLocalPath()
 */
function hook_imagemagick_post_save_alter(\Drupal\imagemagick\ImagemagickExecArguments $arguments) {
}

/**
 * Alter the arguments to ImageMagick command-line executables.
 *
 * This hook is executed just before Imagemagick executables are called.
 * It allows to change file paths for source and destination image files,
 * and/or to alter the command line arguments that are passed to the binaries.
 * The toolkit provides methods to prepend, add, find, get and reset
 * arguments that have already been set by image effects.
 *
 * In addition to arguments that are passed to the binaries command line for
 * execution, it is possible to push arguments to be used only by the toolkit
 * or the hooks. You can add/get/find such arguments by specifying
 * ImagemagickExecArguments::INTERNAL as the argument $mode in the methods.
 *
 * ImageMagick automatically converts the target image to the format denoted by
 * the file extension. However, since changing the file extension is not always
 * an option, you can specify an alternative image format via
 * $arguments->setDestinationFormat('format'), where 'format' is a string
 * denoting an Imagemagick supported format, or via
 * $arguments->setDestinationFormatFromExtension('extension'), where
 * 'extension' is a string denoting an image file extension.
 *
 * When the destination format is set, it is passed to ImageMagick's convert
 * binary with the syntax "[format]:[destination]".
 *
 * @param \Drupal\imagemagick\ImagemagickExecArguments $arguments
 *   The ImageMagick/GraphicsMagick execution arguments object.
 * @param string $command
 *   The ImageMagick/GraphicsMagick command being called.
 *
 * @see http://www.imagemagick.org/script/command-line-processing.php#output
 * @see http://www.imagemagick.org/Usage/files/#save
 *
 * @see \Drupal\imagemagick\ImagemagickExecArguments
 * @see \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit::convert()
 * @see \Drupal\imagemagick\Plugin\FileMetadata\ImagemagickIdentify::identify()
 */
function hook_imagemagick_arguments_alter(\Drupal\imagemagick\ImagemagickExecArguments $arguments, $command) {
}
