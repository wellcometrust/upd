<?php

namespace Drupal\imagemagick;

use Drupal\Component\Utility\Timer;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

/**
 * Manage execution of ImageMagick/GraphicsMagick commands.
 */
class ImagemagickExecManager implements ImagemagickExecManagerInterface {

  use StringTranslationTrait;

  /**
   * Whether we are running on Windows OS.
   *
   * @var bool
   */
  protected $isWindows;

  /**
   * The app root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * The execution timeout.
   *
   * @var int
   */
  protected $timeout = 60;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The format mapper service.
   *
   * @var \Drupal\imagemagick\ImagemagickFormatMapperInterface
   */
  protected $formatMapper;

  /**
   * Constructs an ImagemagickExecManager object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param string $app_root
   *   The app root.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\imagemagick\ImagemagickFormatMapperInterface $format_mapper
   *   The format mapper service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(LoggerInterface $logger, ConfigFactoryInterface $config_factory, $app_root, AccountProxyInterface $current_user, ImagemagickFormatMapperInterface $format_mapper, ModuleHandlerInterface $module_handler) {
    $this->logger = $logger;
    $this->configFactory = $config_factory;
    $this->appRoot = $app_root;
    $this->currentUser = $current_user;
    $this->formatMapper = $format_mapper;
    $this->moduleHandler = $module_handler;
    $this->isWindows = substr(PHP_OS, 0, 3) === 'WIN';
  }

  /**
   * Returns the format mapper.
   *
   * @return \Drupal\imagemagick\ImagemagickFormatMapperInterface
   *   The format mapper service.
   *
   * @todo in 8.x-3.0, add this method to the interface.
   */
  public function getFormatMapper() {
    return $this->formatMapper;
  }

  /**
   * Returns the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler service.
   *
   * @todo in 8.x-3.0, add this method to the interface.
   */
  public function getModuleHandler() {
    return $this->moduleHandler;
  }

  /**
   * Sets the execution timeout (max. runtime).
   *
   * To disable the timeout, set this value to null.
   *
   * @param int|null $timeout
   *   The timeout in seconds.
   *
   * @return $this
   *
   * @todo in 8.x-3.0, add this method to the interface.
   */
  public function setTimeout($timeout) {
    $this->timeout = $timeout;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackage($package = NULL) {
    if ($package === NULL) {
      $package = $this->configFactory->get('imagemagick.settings')->get('binaries');
    }
    return $package;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackageLabel($package = NULL) {
    switch ($this->getPackage($package)) {
      case 'imagemagick':
        return $this->t('ImageMagick');

      case 'graphicsmagick':
        return $this->t('GraphicsMagick');

      default:
        return $package;

    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkPath($path, $package = NULL) {
    $status = [
      'output' => '',
      'errors' => [],
    ];

    // Execute gm or convert based on settings.
    $package = $package ?: $this->getPackage();
    $binary = $package === 'imagemagick' ? 'convert' : 'gm';
    $executable = $this->getExecutable($binary, $path);

    // If a path is given, we check whether the binary exists and can be
    // invoked.
    if (!empty($path)) {
      // Check whether the given file exists.
      if (!is_file($executable)) {
        $status['errors'][] = $this->t('The @suite executable %file does not exist.', ['@suite' => $this->getPackageLabel($package), '%file' => $executable]);
      }
      // If it exists, check whether we can execute it.
      elseif (!is_executable($executable)) {
        $status['errors'][] = $this->t('The @suite file %file is not executable.', ['@suite' => $this->getPackageLabel($package), '%file' => $executable]);
      }
    }

    // In case of errors, check for open_basedir restrictions.
    if ($status['errors'] && ($open_basedir = ini_get('open_basedir'))) {
      $status['errors'][] = $this->t('The PHP <a href=":php-url">open_basedir</a> security restriction is set to %open-basedir, which may prevent to locate the @suite executable.', [
        '@suite' => $this->getPackageLabel($package),
        '%open-basedir' => $open_basedir,
        ':php-url' => 'http://php.net/manual/en/ini.core.php#ini.open-basedir',
      ]);
    }

    // Unless we had errors so far, try to invoke convert.
    if (!$status['errors']) {
      $error = NULL;
      $this->runOsShell($executable, '-version', $package, $status['output'], $error);
      if ($error !== '') {
        $status['errors'][] = $error;
      }
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function execute($command, ImagemagickExecArguments $arguments, &$output = NULL, &$error = NULL, $path = NULL) {
    switch ($command) {
      case 'convert':
        $binary = $this->getPackage() === 'imagemagick' ? 'convert' : 'gm';
        break;

      case 'identify':
        $binary = $this->getPackage() === 'imagemagick' ? 'identify' : 'gm';
        break;

    }
    $cmd = $this->getExecutable($binary, $path);

    if ($source_path = $arguments->getSourceLocalPath()) {
      if (($source_frames = $arguments->getSourceFrames()) !== NULL) {
        $source_path .= $source_frames;
      }
      $source_path = $this->escapeShellArg($source_path);
    }

    if ($destination_path = $arguments->getDestinationLocalPath()) {
      $destination_path = $this->escapeShellArg($destination_path);
      // If the format of the derivative image has to be changed, concatenate
      // the new image format and the destination path, delimited by a colon.
      // @see http://www.imagemagick.org/script/command-line-processing.php#output
      if (($format = $arguments->getDestinationFormat()) !== '') {
        $destination_path = $format . ':' . $destination_path;
      }
    }

    switch ($command) {
      case 'identify':
        switch ($this->getPackage()) {
          case 'imagemagick':
            // @codingStandardsIgnoreStart
            // ImageMagick syntax:
            // identify [arguments] source
            // @codingStandardsIgnoreEnd
            $cmdline = $arguments->toString(ImagemagickExecArguments::PRE_SOURCE);
            // @todo BC layer. In 8.x-3.0, remove adding post source path
            // arguments.
            if (($post = $arguments->toString(ImagemagickExecArguments::POST_SOURCE)) !== '') {
              $cmdline .= ' ' . $post;
            }
            $cmdline .= ' ' . $source_path;
            break;

          case 'graphicsmagick':
            // @codingStandardsIgnoreStart
            // GraphicsMagick syntax:
            // gm identify [arguments] source
            // @codingStandardsIgnoreEnd
            $cmdline = 'identify ' . $arguments->toString(ImagemagickExecArguments::PRE_SOURCE);
            // @todo BC layer. In 8.x-3.0, remove adding post source path
            // arguments.
            if (($post = $arguments->toString(ImagemagickExecArguments::POST_SOURCE)) !== '') {
              $cmdline .= ' ' . $post;
            }
            $cmdline .= ' ' . $source_path;
            break;

        }
        break;

      case 'convert':
        switch ($this->getPackage()) {
          case 'imagemagick':
            // @codingStandardsIgnoreStart
            // ImageMagick syntax:
            // convert input [arguments] output
            // @see http://www.imagemagick.org/Usage/basics/#cmdline
            // @codingStandardsIgnoreEnd
            $cmdline = '';
            if (($pre = $arguments->toString(ImagemagickExecArguments::PRE_SOURCE)) !== '') {
              $cmdline .= $pre . ' ';
            }
            $cmdline .= $source_path . ' ' . $arguments->toString(ImagemagickExecArguments::POST_SOURCE) . ' ' . $destination_path;
            break;

          case 'graphicsmagick':
            // @codingStandardsIgnoreStart
            // GraphicsMagick syntax:
            // gm convert [arguments] input output
            // @see http://www.graphicsmagick.org/GraphicsMagick.html
            // @codingStandardsIgnoreEnd
            $cmdline = 'convert ';
            if (($pre = $arguments->toString(ImagemagickExecArguments::PRE_SOURCE)) !== '') {
              $cmdline .= $pre . ' ';
            }
            $cmdline .= $arguments->toString(ImagemagickExecArguments::POST_SOURCE) . ' ' . $source_path . ' ' . $destination_path;
            break;

        }
        break;

    }

    $return_code = $this->runOsShell($cmd, $cmdline, $this->getPackage(), $output, $error);

    if ($return_code !== FALSE) {
      // If the executable returned a non-zero code, log to the watchdog.
      if ($return_code != 0) {
        if ($error === '') {
          // If there is no error message, and allowed in config, log a
          // warning.
          if ($this->configFactory->get('imagemagick.settings')->get('log_warnings') === TRUE) {
            $this->logger->warning("@suite returned with code @code [command: @command @cmdline]", [
              '@suite' => $this->getPackageLabel(),
              '@code' => $return_code,
              '@command' => $cmd,
              '@cmdline' => $cmdline,
            ]);
          }
        }
        else {
          // Log $error with context information.
          $this->logger->error("@suite error @code: @error [command: @command @cmdline]", [
            '@suite' => $this->getPackageLabel(),
            '@code' => $return_code,
            '@error' => $error,
            '@command' => $cmd,
            '@cmdline' => $cmdline,
          ]);
        }
        // Executable exited with an error code, return FALSE.
        return FALSE;
      }

      // The shell command was executed successfully.
      return TRUE;
    }
    // The shell command could not be executed.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function runOsShell($command, $arguments, $id, &$output = NULL, &$error = NULL) {
    $command_line = $command . ' ' . $arguments;
    $output = '';
    $error = '';

    Timer::start('imagemagick:runOsShell');
    $process = new Process($command_line, $this->appRoot);
    $process->setTimeout($this->timeout);
    try {
      $process->run();
      $output = utf8_encode($process->getOutput());
      $error = utf8_encode($process->getErrorOutput());
      $return_code = $process->getExitCode();
    }
    catch (\Exception $e) {
      $error = $e->getMessage();
      $return_code = $process->getExitCode() ? $process->getExitCode() : 1;
    }
    $execution_time = Timer::stop('imagemagick:runOsShell')['time'];

    // Process debugging information if required.
    if ($this->configFactory->get('imagemagick.settings')->get('debug')) {
      $this->debugMessage('@suite command: <pre>@raw</pre> executed in @execution_timems', [
        '@suite' => $this->getPackageLabel($id),
        '@raw' => print_r($command_line, TRUE),
        '@execution_time' => $execution_time,
      ]);
      if ($output !== '') {
        $this->debugMessage('@suite output: <pre>@raw</pre>', [
          '@suite' => $this->getPackageLabel($id),
          '@raw' => print_r($output, TRUE),
        ]);
      }
      if ($error !== '') {
        $this->debugMessage('@suite error @return_code: <pre>@raw</pre>', [
          '@suite' => $this->getPackageLabel($id),
          '@return_code' => $return_code,
          '@raw' => print_r($error, TRUE),
        ]);
      }
    }

    return $return_code;
  }

  /**
   * Logs a debug message, and shows it on the screen for authorized users.
   *
   * @param string $message
   *   The debug message.
   * @param string[] $context
   *   Context information.
   */
  public function debugMessage($message, array $context) {
    $this->logger->debug($message, $context);
    if ($this->currentUser->hasPermission('administer site configuration')) {
      // Strips raw text longer than 10 lines to optimize displaying.
      if (isset($context['@raw'])) {
        $raw = explode("\n", $context['@raw']);
        if (count($raw) > 10) {
          $tmp = [];
          for ($i = 0; $i < 9; $i++) {
            $tmp[] = $raw[$i];
          }
          $tmp[] = (string) $this->t('[Further text stripped. The watchdog log has the full text.]');
          $context['@raw'] = implode("\n", $tmp);
        }
      }
      // @codingStandardsIgnoreLine
      drupal_set_message($this->t($message, $context), 'status', TRUE);
    }
  }

  /**
   * Gets the list of locales installed on the server.
   *
   * @return string
   *   The string resulting from the execution of 'locale -a' in *nix systems.
   */
  public function getInstalledLocales() {
    $output = '';
    if ($this->isWindows === FALSE) {
      $this->runOsShell('locale', '-a', 'locale', $output);
    }
    else {
      $output = (string) $this->t("List not available on Windows servers.");
    }
    return $output;
  }

  /**
   * Returns the full path to the executable.
   *
   * @param string $binary
   *   The program to execute, typically 'convert', 'identify' or 'gm'.
   * @param string $path
   *   (optional) A custom path to the folder of the executable. When left
   *   empty, the setting imagemagick.settings.path_to_binaries is taken.
   *
   * @return string
   *   The full path to the executable.
   */
  protected function getExecutable($binary, $path = NULL) {
    // $path is only passed from the validation of the image toolkit form, on
    // which the path to convert is configured. @see ::checkPath()
    if (!isset($path)) {
      $path = $this->configFactory->get('imagemagick.settings')->get('path_to_binaries');
    }

    $executable = $binary;
    if ($this->isWindows) {
      $executable .= '.exe';
    }

    return $path . $executable;
  }

  /**
   * Escapes a string.
   *
   * PHP escapeshellarg() drops non-ascii characters, this is a replacement.
   *
   * Stop-gap replacement until core issue #1561214 has been solved. Solution
   * proposed in #1502924-8.
   *
   * PHP escapeshellarg() on Windows also drops % (percentage sign) characters.
   * We prevent this by replacing it with a pattern that should be highly
   * unlikely to appear in the string itself and does not contain any
   * "dangerous" character at all (very wide definition of dangerous). After
   * escaping we replace that pattern back with a % character.
   *
   * @param string $arg
   *   The string to escape.
   *
   * @return string
   *   An escaped string for use in the ::execute method.
   */
  public function escapeShellArg($arg) {
    static $percentage_sign_replace_pattern = '1357902468IMAGEMAGICKPERCENTSIGNPATTERN8642097531';

    // Put the configured locale in a static to avoid multiple config get calls
    // in the same request.
    static $config_locale;

    if (!isset($config_locale)) {
      $config_locale = $this->configFactory->get('imagemagick.settings')->get('locale');
      if (empty($config_locale)) {
        $config_locale = FALSE;
      }
    }

    if ($this->isWindows) {
      // Temporarily replace % characters.
      $arg = str_replace('%', $percentage_sign_replace_pattern, $arg);
    }

    // If no locale specified in config, return with standard.
    if ($config_locale === FALSE) {
      $arg_escaped = escapeshellarg($arg);
    }
    else {
      // Get the current locale.
      $current_locale = setlocale(LC_CTYPE, 0);
      if ($current_locale != $config_locale) {
        // Temporarily swap the current locale with the configured one.
        setlocale(LC_CTYPE, $config_locale);
        $arg_escaped = escapeshellarg($arg);
        setlocale(LC_CTYPE, $current_locale);
      }
      else {
        $arg_escaped = escapeshellarg($arg);
      }
    }

    // Get our % characters back.
    if ($this->isWindows) {
      $arg_escaped = str_replace($percentage_sign_replace_pattern, '%', $arg_escaped);
    }

    return $arg_escaped;
  }

}
