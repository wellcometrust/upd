<?php

namespace Drupal\mailchimp;

use ReflectionClass;

/**
 * Classloader for Mailchimp test classes.
 */
class Autoload {

  /**
   * Registers this class as an autoloader.
   */
  public static function register() {
    spl_autoload_register([__CLASS__, 'loadClass']);
  }

  /**
   * Unregisters this class as an autoloader.
   */
  public static function unregister() {
    spl_autoload_unregister([__CLASS__, 'loadClass']);
  }

  /**
   * Autoload callback. Only loads Mailchimp test classes.
   *
   * @param string $class
   *   The name of the class to load.
   */
  public static function loadClass($class) {
    if (strpos($class, 'Mailchimp\\Tests') === 0 && class_exists('Mailchimp\\Mailchimp')) {
      // Find out where the mailchimp library is defined.
      $reflector = new ReflectionClass('Mailchimp\\Mailchimp');
      $library_dir = dirname(dirname($reflector->getFileName()));

      // Compose expected path to the test class.
      $parts = explode('\\', $class);
      $class_without_base = implode('\\', array_slice($parts, 2));
      $file = $library_dir . '/tests/src/' . strtr($class_without_base, '\\', '/') . '.php';

      // Include class file if it exists.
      if (file_exists($file)) {
        require_once $file;
        return;
      }
    }
  }

}
