<?php

namespace Drupal\mailchimp;

use Drupal\Core\Config\Config;
use Drupal\mailchimp\Exception\ClientFactoryException;
use Mailchimp\Mailchimp;
use Psr\Log\LoggerInterface;

/**
 * Factory for Mailchimp PHP Library.
 */
class ClientFactory {

  /**
   * Mailchimp Settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Mailchimp logging interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Mailchimp Library instances, keyed by class name.
   *
   * @var array
   */
  protected $instances = [];

  /**
   * ClientFactory constructor.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Mailchimp Settings.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logging interface.
   */
  public function __construct(Config $config, LoggerInterface $logger) {
    $this->config = $config;
    $this->logger = $logger;
  }

  /**
   * Retrieve a Mailchimp Library class by classname.
   *
   * @param string $classname
   *   Relative class name for a Mailchimp Library object.
   *
   * @return \Mailchimp\Mailchimp
   *   Mailchimp Library.
   *
   * @throws \Drupal\mailchimp\Exception\ClientFactoryException
   */
  public function getByClassName(string $classname = 'Mailchimp'): Mailchimp {
    return $this->getInstance($this->resolveClass($classname));
  }

  /**
   * Wrapper around getByClassName() to handle exceptions.
   *
   * @param string $classname
   *   Relative class name for a Mailchimp Library object.
   *
   * @return \Mailchimp\Mailchimp|null
   *   Mailchimp Library or Null.
   */
  public function getByClassNameOrNull(string $classname = 'Mailchimp') {
    try {
      return $this->getByClassName($classname);
    }
    catch (ClientFactoryException $e) {
      return NULL;
    }
  }

  /**
   * Loads an instance of a Mailchimp Library object, creating if necessary.
   *
   * @param string $class
   *   Explicit class name for a Mailchimp Library object.
   *
   * @return \Mailchimp\Mailchimp
   *   Mailchimp Library.
   *
   * @throws \Drupal\mailchimp\Exception\ClientFactoryException
   */
  protected function getInstance(string $class): Mailchimp {
    if (!isset($this->instances[$class])) {
      $this->instances[$class] = $this->createInstance($class);
    }

    return $this->instances[$class];
  }

  /**
   * Instantiates a new instance of a Mailchimp Library class.
   *
   * @param string $class
   *   Relative class name for a Mailchimp Library object.
   *
   * @return \Mailchimp\Mailchimp
   *   Mailchimp Library.
   *
   * @throws \Drupal\mailchimp\Exception\ClientFactoryException
   */
  protected function createInstance(string $class): Mailchimp {
    $api_key = $this->config->get('api_key');
    if (!strlen($api_key)) {
      $this->logger->error('Mailchimp API Key cannot be blank.');
      throw new ClientFactoryException('Mailchimp API Key cannot be blank');
    }

    $http_options = [
      'timeout' => $this->config->get('api_timeout'),
      'headers' => [
        'User-Agent' => _mailchimp_get_user_agent(),
      ],
    ];

    return new $class($api_key, 'apikey', $http_options);
  }

  /**
   * Classname autoloader to enforce test mode when configured.
   *
   * @param string $classname
   *   Relative class name for a Mailchimp Library object.
   *
   * @return string
   *   Explicit class name.
   */
  protected function resolveClass(string $classname): string {
    if ($this->config->get('test_mode')) {
      // Register autoloader for loading test classes.
      Autoload::register();
      $classname = '\Mailchimp\Tests\\' . $classname;
    }
    else {
      $classname = '\Mailchimp\\' . $classname;
    }

    return $classname;
  }

}
