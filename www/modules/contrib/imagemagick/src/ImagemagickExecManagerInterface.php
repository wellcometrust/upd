<?php

namespace Drupal\imagemagick;

/**
 * Provides an interface for ImageMagick execution managers.
 */
interface ImagemagickExecManagerInterface {

  /**
   * Gets the binaries package in use.
   *
   * @param string $package
   *   (optional) Force the graphics package.
   *
   * @return string
   *   The default package ('imagemagick'|'graphicsmagick'), or the $package
   *   argument.
   */
  public function getPackage($package = NULL);

  /**
   * Gets a translated label of the binaries package in use.
   *
   * @param string $package
   *   (optional) Force the package.
   *
   * @return string
   *   A translated label of the binaries package in use, or the $package
   *   argument.
   */
  public function getPackageLabel($package = NULL);

  /**
   * Verifies file path of the executable binary by checking its version.
   *
   * @param string $path
   *   The user-submitted file path to the convert binary.
   * @param string $package
   *   (optional) The graphics package to use.
   *
   * @return array
   *   An associative array containing:
   *   - output: The shell output of 'convert -version', if any.
   *   - errors: A list of error messages indicating if the executable could
   *     not be found or executed.
   */
  public function checkPath($path, $package = NULL);

  /**
   * Executes the convert executable as shell command.
   *
   * @param string $command
   *   The executable to run.
   * @param \Drupal\imagemagick\ImagemagickExecArguments $arguments
   *   An ImageMagick execution arguments object.
   * @param string &$output
   *   (optional) A variable to assign the shell stdout to, passed by
   *   reference.
   * @param string &$error
   *   (optional) A variable to assign the shell stderr to, passed by
   *   reference.
   * @param string $path
   *   (optional) A custom file path to the executable binary.
   *
   * @return bool
   *   TRUE if the command succeeded, FALSE otherwise. The error exit status
   *   code integer returned by the executable is logged.
   */
  public function execute($command, ImagemagickExecArguments $arguments, &$output = NULL, &$error = NULL, $path = NULL);

  /**
   * Executes a command on the operating system.
   *
   * This differs from ::runOsCommand in the sense that here the command to be
   * executed and its arguments are passed separately.
   *
   * @param string $command
   *   The command to run.
   * @param string $arguments
   *   The arguments of the command to run.
   * @param string $id
   *   An identifier for the process to be spawned on the operating system.
   * @param string &$output
   *   (optional) A variable to assign the shell stdout to, passed by
   *   reference.
   * @param string &$error
   *   (optional) A variable to assign the shell stderr to, passed by
   *   reference.
   *
   * @return int|bool
   *   The operating system returned code, or FALSE if it was not possible to
   *   execute the command.
   */
  public function runOsShell($command, $arguments, $id, &$output = NULL, &$error = NULL);

}
