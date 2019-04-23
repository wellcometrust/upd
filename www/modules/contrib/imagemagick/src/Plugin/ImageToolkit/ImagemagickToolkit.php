<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\ImageToolkit\ImageToolkitBase;
use Drupal\Core\ImageToolkit\ImageToolkitOperationManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\file_mdm\FileMetadataManagerInterface;
use Drupal\imagemagick\ImagemagickExecArguments;
use Drupal\imagemagick\ImagemagickExecManagerInterface;
use Drupal\imagemagick\ImagemagickFormatMapperInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides ImageMagick integration toolkit for image manipulation.
 *
 * @ImageToolkit(
 *   id = "imagemagick",
 *   title = @Translation("ImageMagick image toolkit")
 * )
 */
class ImagemagickToolkit extends ImageToolkitBase {

  /**
   * EXIF orientation not fetched.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   parseFileViaIdentify() to parse image files.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2941093
   */
  const EXIF_ORIENTATION_NOT_FETCHED = -99;

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
   * The file metadata manager service.
   *
   * @var \Drupal\file_mdm\FileMetadataManagerInterface
   */
  protected $fileMetadataManager;

  /**
   * The ImageMagick execution manager service.
   *
   * @var \Drupal\imagemagick\ImagemagickExecManagerInterface
   */
  protected $execManager;

  /**
   * The execution arguments object.
   *
   * @var \Drupal\imagemagick\ImagemagickExecArguments
   */
  protected $arguments;

  /**
   * The width of the image.
   *
   * @var int
   */
  protected $width;

  /**
   * The height of the image.
   *
   * @var int
   */
  protected $height;

  /**
   * The number of frames of the source image, for multi-frame images.
   *
   * @var int
   */
  protected $frames;

  /**
   * Image orientation retrieved from EXIF information.
   *
   * @var int
   */
  protected $exifOrientation;

  /**
   * The source image colorspace.
   *
   * @var string
   */
  protected $colorspace;

  /**
   * The source image profiles.
   *
   * @var string[]
   */
  protected $profiles = [];

  /**
   * Constructs an ImagemagickToolkit object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\ImageToolkit\ImageToolkitOperationManagerInterface $operation_manager
   *   The toolkit operation manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\imagemagick\ImagemagickFormatMapperInterface $format_mapper
   *   The format mapper service.
   * @param \Drupal\file_mdm\FileMetadataManagerInterface $file_metadata_manager
   *   The file metadata manager service.
   * @param \Drupal\imagemagick\ImagemagickExecManagerInterface $exec_manager
   *   The ImageMagick execution manager service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ImageToolkitOperationManagerInterface $operation_manager, LoggerInterface $logger, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, ImagemagickFormatMapperInterface $format_mapper, FileMetadataManagerInterface $file_metadata_manager, ImagemagickExecManagerInterface $exec_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $operation_manager, $logger, $config_factory);
    $this->moduleHandler = $module_handler;
    $this->formatMapper = $format_mapper;
    $this->fileMetadataManager = $file_metadata_manager;
    $this->execManager = $exec_manager;
    $this->arguments = new ImagemagickExecArguments($this->execManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('image.toolkit.operation.manager'),
      $container->get('logger.channel.image'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('imagemagick.format_mapper'),
      $container->get('file_metadata_manager'),
      $container->get('imagemagick.exec_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('imagemagick.settings');

    $form['imagemagick'] = [
      '#markup' => $this->t("<a href=':im-url'>ImageMagick</a> and <a href=':gm-url'>GraphicsMagick</a> are stand-alone packages for image manipulation. At least one of them must be installed on the server, and you need to know where it is located. Consult your server administrator or hosting provider for details.", [
        ':im-url' => 'http://www.imagemagick.org',
        ':gm-url' => 'http://www.graphicsmagick.org',
      ]),
    ];
    $form['quality'] = [
      '#type' => 'number',
      '#title' => $this->t('Image quality'),
      '#size' => 10,
      '#min' => 0,
      '#max' => 100,
      '#maxlength' => 3,
      '#default_value' => $config->get('quality'),
      '#field_suffix' => '%',
      '#description' => $this->t('Define the image quality of processed images. Ranges from 0 to 100. Higher values mean better image quality but bigger files.'),
    ];

    // Settings tabs.
    $form['imagemagick_settings'] = [
      '#type' => 'vertical_tabs',
      '#tree' => FALSE,
    ];

    // Graphics suite to use.
    $form['suite'] = [
      '#type' => 'details',
      '#title' => $this->t('Graphics package'),
      '#group' => 'imagemagick_settings',
    ];
    $options = [
      'imagemagick' => $this->getExecManager()->getPackageLabel('imagemagick'),
      'graphicsmagick' => $this->getExecManager()->getPackageLabel('graphicsmagick'),
    ];
    $form['suite']['binaries'] = [
      '#type' => 'radios',
      '#title' => $this->t('Suite'),
      '#default_value' => $this->getExecManager()->getPackage(),
      '#options' => $options,
      '#required' => TRUE,
      '#description' => $this->t("Select the graphics package to use."),
    ];
    // Path to binaries.
    $form['suite']['path_to_binaries'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to the package executables'),
      '#default_value' => $config->get('path_to_binaries'),
      '#required' => FALSE,
      '#description' => $this->t('If needed, the path to the package executables (<kbd>convert</kbd>, <kbd>identify</kbd>, <kbd>gm</kbd>, etc.), <b>including</b> the trailing slash/backslash. For example: <kbd>/usr/bin/</kbd> or <kbd>C:\Program Files\ImageMagick-6.3.4-Q16\</kbd>.'),
    ];
    // Version information.
    $status = $this->getExecManager()->checkPath($this->configFactory->get('imagemagick.settings')->get('path_to_binaries'));
    if (empty($status['errors'])) {
      $version_info = explode("\n", preg_replace('/\r/', '', Html::escape($status['output'])));
    }
    else {
      $version_info = $status['errors'];
    }
    $form['suite']['version'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#open' => TRUE,
      '#title' => $this->t('Version information'),
      '#description' => '<pre>' . implode('<br />', $version_info) . '</pre>',
    ];

    // Image formats.
    $form['formats'] = [
      '#type' => 'details',
      '#title' => $this->t('Image formats'),
      '#group' => 'imagemagick_settings',
    ];
    // Image formats enabled in the toolkit.
    $form['formats']['enabled'] = [
      '#type' => 'item',
      '#title' => $this->t('Currently enabled images'),
      '#description' => $this->t("@suite formats: %formats<br />Image file extensions: %extensions", [
        '%formats' => implode(', ', $this->formatMapper->getEnabledFormats()),
        '%extensions' => Unicode::strtolower(implode(', ', static::getSupportedExtensions())),
        '@suite' => $this->getExecManager()->getPackageLabel(),
      ]),
    ];
    // Image formats map.
    $form['formats']['mapping'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#open' => TRUE,
      '#title' => $this->t('Enable/disable image formats'),
      '#description' => $this->t("Edit the map below to enable/disable image formats. Enabled image file extensions will be determined by the enabled formats, through their MIME types. More information in the module's README.txt"),
    ];
    $form['formats']['mapping']['image_formats'] = [
      '#type' => 'textarea',
      '#rows' => 15,
      '#default_value' => Yaml::encode($config->get('image_formats')),
    ];
    // Image formats supported by the package.
    if (empty($status['errors'])) {
      $this->arguments()->add('-list format', ImagemagickExecArguments::PRE_SOURCE);
      $output = NULL;
      $this->getExecManager()->execute('convert', $this->arguments(), $output);
      $this->arguments()->reset();
      $formats_info = implode('<br />', explode("\n", preg_replace('/\r/', '', Html::escape($output))));
      $form['formats']['list'] = [
        '#type' => 'details',
        '#collapsible' => TRUE,
        '#open' => FALSE,
        '#title' => $this->t('Format list'),
        '#description' => $this->t("Supported image formats returned by executing <kbd>'convert -list format'</kbd>. <b>Note:</b> these are the formats supported by the installed @suite executable, <b>not</b> by the toolkit.<br /><br />", ['@suite' => $this->getExecManager()->getPackageLabel()]),
      ];
      $form['formats']['list']['list'] = [
        '#markup' => "<pre>" . $formats_info . "</pre>",
      ];
    }

    // Execution options.
    $form['exec'] = [
      '#type' => 'details',
      '#title' => $this->t('Execution options'),
      '#group' => 'imagemagick_settings',
    ];

    // Use 'identify' command.
    $form['exec']['use_identify'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use "identify"'),
      '#default_value' => $config->get('use_identify'),
      '#description' => $this->t('<strong>This setting is deprecated and will be removed in the next major release of the Imagemagick module. Leave it enabled to ensure smooth transition.</strong>') . ' ' . $this->t('Use the <kbd>identify</kbd> command to parse image files to determine image format and dimensions. If not selected, the PHP <kbd>getimagesize</kbd> function will be used, BUT this will limit the image formats supported by the toolkit.'),
    ];
    // Cache metadata.
    $configure_link = Link::fromTextAndUrl(
      $this->t('Configure File Metadata Manager'),
      Url::fromRoute('file_mdm.settings')
    );
    $form['exec']['metadata_caching'] = [
      '#type' => 'item',
      '#title' => $this->t("Cache image metadata"),
      '#description' => $this->t("The File Metadata Manager module allows to cache image metadata. This reduces file I/O and <kbd>shell</kbd> calls. @configure.", [
        '@configure' => $configure_link->toString(),
      ]),
    ];
    // Prepend arguments.
    $form['exec']['prepend'] = [
      '#type' => 'details',
      '#collapsible' => FALSE,
      '#open' => TRUE,
      '#title' => $this->t('Prepend arguments'),
      '#description' => $this->t("Use this to add e.g. <kbd><a href=':limit-url'>-limit</a></kbd> or <kbd><a href=':debug-url'>-debug</a></kbd> arguments in front of the others when executing the <kbd>identify</kbd> and <kbd>convert</kbd> commands. Select 'Before source' to execute the arguments before loading the source image.", [
        ':limit-url' => 'https://www.imagemagick.org/script/command-line-options.php#limit',
        ':debug-url' => 'https://www.imagemagick.org/script/command-line-options.php#debug',
      ]),
    ];
    $form['exec']['prepend']['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];
    $form['exec']['prepend']['container']['prepend'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Arguments'),
      '#default_value' => $config->get('prepend'),
      '#required' => FALSE,
    ];
    $form['exec']['prepend']['container']['prepend_pre_source'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Before source'),
      '#default_value' => $config->get('prepend_pre_source'),
    ];

    // Locale.
    $form['exec']['locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Locale'),
      '#default_value' => $config->get('locale'),
      '#required' => FALSE,
      '#description' => $this->t("The locale to be used to prepare the command passed to executables. The default, <kbd>'en_US.UTF-8'</kbd>, should work in most cases. If that is not available on the server, enter another locale. 'Installed Locales' below provides a list of locales installed on the server."),
    ];
    // Installed locales.
    $locales = $this->getExecManager()->getInstalledLocales();
    $locales_info = implode('<br />', explode("\n", preg_replace('/\r/', '', Html::escape($locales))));
    $form['exec']['installed_locales'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#open' => FALSE,
      '#title' => $this->t('Installed locales'),
      '#description' => $this->t("This is the list of all locales available on this server. It is the output of executing <kbd>'locale -a'</kbd> on the operating system."),
    ];
    $form['exec']['installed_locales']['list'] = [
      '#markup' => "<pre>" . $locales_info . "</pre>",
    ];
    // Log warnings.
    $form['exec']['log_warnings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log warnings'),
      '#default_value' => $config->get('log_warnings'),
      '#description' => $this->t('Log a warning entry in the watchdog when the execution of a command returns with a non-zero code, but no error message.'),
    ];
    // Debugging.
    $form['exec']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display debugging information'),
      '#default_value' => $config->get('debug'),
      '#description' => $this->t('Shows commands and their output to users with the %permission permission.', [
        '%permission' => $this->t('Administer site configuration'),
      ]),
    ];

    // Advanced image settings.
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced image settings'),
      '#group' => 'imagemagick_settings',
    ];
    $form['advanced']['density'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Change image resolution to 72 ppi'),
      '#default_value' => $config->get('advanced.density'),
      '#return_value' => 72,
      '#description' => $this->t("Resamples the image <a href=':help-url'>density</a> to a resolution of 72 pixels per inch, the default for web images. Does not affect the pixel size or quality.", [
        ':help-url' => 'http://www.imagemagick.org/script/command-line-options.php#density',
      ]),
    ];
    $form['advanced']['colorspace'] = [
      '#type' => 'select',
      '#title' => $this->t('Convert colorspace'),
      '#default_value' => $config->get('advanced.colorspace'),
      '#options' => [
        'RGB' => $this->t('RGB'),
        'sRGB' => $this->t('sRGB'),
        'GRAY' => $this->t('Gray'),
      ],
      '#empty_value' => 0,
      '#empty_option' => $this->t('- Original -'),
      '#description' => $this->t("Converts processed images to the specified <a href=':help-url'>colorspace</a>. The color profile option overrides this setting.", [
        ':help-url' => 'http://www.imagemagick.org/script/command-line-options.php#colorspace',
      ]),
      '#states' => [
        'enabled' => [
          ':input[name="imagemagick[advanced][profile]"]' => ['value' => ''],
        ],
      ],
    ];
    $form['advanced']['profile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Color profile path'),
      '#default_value' => $config->get('advanced.profile'),
      '#description' => $this->t("The path to a <a href=':help-url'>color profile</a> file that all processed images will be converted to. Leave blank to disable. Use a <a href=':color-url'>sRGB profile</a> to correct the display of professional images and photography.", [
        ':help-url' => 'http://www.imagemagick.org/script/command-line-options.php#profile',
        ':color-url' => 'http://www.color.org/profiles.html',
      ]),
    ];

    return $form;
  }

  /**
   * Returns the ImageMagick execution manager service.
   *
   * @return \Drupal\imagemagick\ImagemagickExecManagerInterface
   *   The ImageMagick execution manager service.
   */
  public function getExecManager() {
    return $this->execManager;
  }

  /**
   * Gets the binaries package in use.
   *
   * @param string $package
   *   (optional) Force the graphics package.
   *
   * @return string
   *   The default package ('imagemagick'|'graphicsmagick'), or the $package
   *   argument.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecManagerInterface::getPackage() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function getPackage($package = NULL) {
    @trigger_error('getPackage() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecManagerInterface::getPackage() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->getExecManager()->getPackage($package);
  }

  /**
   * Gets a translated label of the binaries package in use.
   *
   * @param string $package
   *   (optional) Force the package.
   *
   * @return string
   *   A translated label of the binaries package in use, or the $package
   *   argument.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecManagerInterface::getPackageLabel() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function getPackageLabel($package = NULL) {
    @trigger_error('getPackageLabel() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecManagerInterface::getPackageLabel() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->getExecManager()->getPackageLabel($package);
  }

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
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecManagerInterface::checkPath() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function checkPath($path, $package = NULL) {
    @trigger_error('checkPath() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecManagerInterface::checkPath() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->getExecManager()->checkPath($path, $package);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    try {
      // Check that the format map contains valid YAML.
      $image_formats = Yaml::decode($form_state->getValue([
        'imagemagick', 'formats', 'mapping', 'image_formats',
      ]));
      // Validate the enabled image formats.
      $errors = $this->formatMapper->validateMap($image_formats);
      if ($errors) {
        $form_state->setErrorByName('imagemagick][formats][mapping][image_formats', new FormattableMarkup("<pre>Image format errors:<br/>@errors</pre>", ['@errors' => Yaml::encode($errors)]));
      }
    }
    catch (InvalidDataTypeException $e) {
      // Invalid YAML detected, show details.
      $form_state->setErrorByName('imagemagick][formats][mapping][image_formats', $this->t("YAML syntax error: @error", ['@error' => $e->getMessage()]));
    }
    // Validate the binaries path only if this toolkit is selected, otherwise
    // it will prevent the entire image toolkit selection form from being
    // submitted.
    if ($form_state->getValue(['image_toolkit']) === 'imagemagick') {
      $status = $this->getExecManager()->checkPath($form_state->getValue([
        'imagemagick', 'suite', 'path_to_binaries',
      ]), $form_state->getValue(['imagemagick', 'suite', 'binaries']));
      if ($status['errors']) {
        $form_state->setErrorByName('imagemagick][suite][path_to_binaries', new FormattableMarkup(implode('<br />', $status['errors']), []));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('imagemagick.settings');
    $config
      ->set('quality', (int) $form_state->getValue([
        'imagemagick', 'quality',
      ]))
      ->set('binaries', (string) $form_state->getValue([
        'imagemagick', 'suite', 'binaries',
      ]))
      ->set('path_to_binaries', (string) $form_state->getValue([
        'imagemagick', 'suite', 'path_to_binaries',
      ]))
      ->set('use_identify', (bool) $form_state->getValue([
        'imagemagick', 'exec', 'use_identify',
      ]))
      ->set('image_formats', Yaml::decode($form_state->getValue([
        'imagemagick', 'formats', 'mapping', 'image_formats',
      ])))
      ->set('prepend', (string) $form_state->getValue([
        'imagemagick', 'exec', 'prepend', 'container', 'prepend',
      ]))
      ->set('prepend_pre_source', (bool) $form_state->getValue([
        'imagemagick', 'exec', 'prepend', 'container', 'prepend_pre_source',
      ]))
      ->set('locale', (string) $form_state->getValue([
        'imagemagick', 'exec', 'locale',
      ]))
      ->set('log_warnings', (bool) $form_state->getValue([
        'imagemagick', 'exec', 'log_warnings',
      ]))
      ->set('debug', (bool) $form_state->getValue([
        'imagemagick', 'exec', 'debug',
      ]))
      ->set('advanced.density', (int) $form_state->getValue([
        'imagemagick', 'advanced', 'density',
      ]))
      ->set('advanced.colorspace', (string) $form_state->getValue([
        'imagemagick', 'advanced', 'colorspace',
      ]))
      ->set('advanced.profile', (string) $form_state->getValue([
        'imagemagick', 'advanced', 'profile',
      ]));
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {
    return ((bool) $this->getMimeType());
  }

  /**
   * {@inheritdoc}
   */
  public function setSource($source) {
    parent::setSource($source);
    $this->arguments()->setSource($source);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    return $this->arguments()->getSource();
  }

  /**
   * Gets the local filesystem path to the image file.
   *
   * @return string
   *   A filesystem path.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ::ensureSourceLocalPath() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function getSourceLocalPath() {
    @trigger_error('getSourceLocalPath() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ::ensureSourceLocalPath() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->ensureSourceLocalPath();
  }

  /**
   * Ensures that the local filesystem path to the image file exists.
   *
   * @return string
   *   A filesystem path.
   */
  public function ensureSourceLocalPath() {
    // If sourceLocalPath is NULL, then ensure it is prepared. This can
    // happen if image was identified via cached metadata: the cached data are
    // available, but the temp file path is not resolved, or even the temp file
    // could be missing if it was copied locally from a remote file system.
    if (!$this->arguments()->getSourceLocalPath() && $this->getSource()) {
      $this->moduleHandler->alter('imagemagick_pre_parse_file', $this->arguments);
    }
    return $this->arguments()->getSourceLocalPath();
  }

  /**
   * Sets the local filesystem path to the image file.
   *
   * @param string $path
   *   A filesystem path.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::setSourceLocalPath() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function setSourceLocalPath($path) {
    @trigger_error('setSourceLocalPath() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::setSourceLocalPath() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    $this->arguments()->setSourceLocalPath($path);
    return $this;
  }

  /**
   * Gets the source image format.
   *
   * @return string
   *   The source image format.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::getSourceFormat() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function getSourceFormat() {
    @trigger_error('getSourceFormat() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::getSourceFormat() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->arguments()->getSourceFormat();
  }

  /**
   * Sets the source image format.
   *
   * @param string $format
   *   The image format.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::setSourceFormat() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function setSourceFormat($format) {
    @trigger_error('setSourceFormat() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::setSourceFormat() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    $this->arguments()->setSourceFormat($format);
    return $this;
  }

  /**
   * Sets the source image format from an image file extension.
   *
   * @param string $extension
   *   The image file extension.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::setSourceFormatFromExtension() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function setSourceFormatFromExtension($extension) {
    @trigger_error('setSourceFormatFromExtension() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::setSourceFormatFromExtension() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    $this->arguments()->setSourceFormatFromExtension($extension);
    return $this;
  }

  /**
   * Gets the source EXIF orientation.
   *
   * @return int
   *   The source EXIF orientation.
   */
  public function getExifOrientation() {
    if ($this->exifOrientation === static::EXIF_ORIENTATION_NOT_FETCHED) {
      if ($this->getSource() !== NULL) {
        $file_md = $this->fileMetadataManager->uri($this->getSource());
        if ($file_md->getLocalTempPath() === NULL) {
          $file_md->setLocalTempPath($this->ensureSourceLocalPath());
        }
        $orientation = $file_md->getMetadata('exif', 'Orientation');
        $this->setExifOrientation(isset($orientation['value']) ? $orientation['value'] : NULL);
      }
      else {
        $this->setExifOrientation(NULL);
      }
    }
    return $this->exifOrientation;
  }

  /**
   * Sets the source EXIF orientation.
   *
   * @param int|null $exif_orientation
   *   The EXIF orientation.
   *
   * @return $this
   */
  public function setExifOrientation($exif_orientation) {
    $this->exifOrientation = $exif_orientation ? (int) $exif_orientation : NULL;
    return $this;
  }

  /**
   * Gets the source colorspace.
   *
   * @return string
   *   The source colorspace.
   */
  public function getColorspace() {
    return $this->colorspace;
  }

  /**
   * Sets the source colorspace.
   *
   * @param string $colorspace
   *   The image colorspace.
   *
   * @return $this
   */
  public function setColorspace($colorspace) {
    $this->colorspace = Unicode::strtoupper($colorspace);
    return $this;
  }

  /**
   * Gets the source profiles.
   *
   * @return string[]
   *   The source profiles.
   */
  public function getProfiles() {
    return $this->profiles;
  }

  /**
   * Sets the source profiles.
   *
   * @param array $profiles
   *   The image profiles.
   *
   * @return $this
   */
  public function setProfiles(array $profiles) {
    $this->profiles = $profiles;
    return $this;
  }

  /**
   * Gets the source image number of frames.
   *
   * @return int
   *   The number of frames of the image.
   */
  public function getFrames() {
    return $this->frames;
  }

  /**
   * Sets the source image number of frames.
   *
   * @param int|null $frames
   *   The number of frames of the image.
   *
   * @return $this
   */
  public function setFrames($frames) {
    $this->frames = $frames;
    return $this;
  }

  /**
   * Gets the image destination URI/path on saving.
   *
   * @return string
   *   The image destination URI/path.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::getDestination() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function getDestination() {
    @trigger_error('getDestination() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::getDestination() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->arguments()->getDestination();
  }

  /**
   * Sets the image destination URI/path on saving.
   *
   * @param string $destination
   *   The image destination URI/path.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::setDestination() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function setDestination($destination) {
    @trigger_error('setDestination() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::setDestination() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    $this->arguments()->setDestination($destination);
    return $this;
  }

  /**
   * Gets the local filesystem path to the destination image file.
   *
   * @return string
   *   A filesystem path.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::getDestinationLocalPath() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function getDestinationLocalPath() {
    @trigger_error('getDestinationLocalPath() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::getDestinationLocalPath() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->arguments()->getDestinationLocalPath();
  }

  /**
   * Sets the local filesystem path to the destination image file.
   *
   * @param string $path
   *   A filesystem path.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::setDestinationLocalPath() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function setDestinationLocalPath($path) {
    @trigger_error('setDestinationLocalPath() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::setDestinationLocalPath() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    $this->arguments()->setDestinationLocalPath($path);
    return $this;
  }

  /**
   * Gets the image destination format.
   *
   * When set, it is passed to the convert binary in the syntax
   * "[format]:[destination]", where [format] is a string denoting an
   * ImageMagick's image format.
   *
   * @return string
   *   The image destination format.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::getDestinationFormat() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function getDestinationFormat() {
    @trigger_error('getDestinationFormat() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::getDestinationFormat() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    return $this->arguments()->getDestinationFormat();
  }

  /**
   * Sets the image destination format.
   *
   * When set, it is passed to the convert binary in the syntax
   * "[format]:[destination]", where [format] is a string denoting an
   * ImageMagick's image format.
   *
   * @param string $format
   *   The image destination format.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::setDestinationFormat() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function setDestinationFormat($format) {
    @trigger_error('setDestinationFormat() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::setDestinationFormat() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    $this->arguments()->setDestinationFormat($this->formatMapper->isFormatEnabled($format) ? $format : '');
    return $this;
  }

  /**
   * Sets the image destination format from an image file extension.
   *
   * When set, it is passed to the convert binary in the syntax
   * "[format]:[destination]", where [format] is a string denoting an
   * ImageMagick's image format.
   *
   * @param string $extension
   *   The destination image file extension.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImagemagickExecArguments::setDestinationFormatFromExtension() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938375
   */
  public function setDestinationFormatFromExtension($extension) {
    @trigger_error('setDestinationFormatFromExtension() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImagemagickExecArguments::setDestinationFormatFromExtension() instead. See https://www.drupal.org/project/imagemagick/issues/2938375.', E_USER_DEPRECATED);
    $this->arguments()->setDestinationFormatFromExtension($extension);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidth() {
    return $this->width;
  }

  /**
   * Sets image width.
   *
   * @param int $width
   *   The image width.
   *
   * @return $this
   */
  public function setWidth($width) {
    $this->width = $width;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * Sets image height.
   *
   * @param int $height
   *   The image height.
   *
   * @return $this
   */
  public function setHeight($height) {
    $this->height = $height;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMimeType() {
    return $this->formatMapper->getMimeTypeFromFormat($this->arguments()->getSourceFormat());
  }

  /**
   * Returns the current ImagemagickExecArguments object.
   *
   * @return \Drupal\imagemagick\ImagemagickExecArguments
   *   The current ImagemagickExecArguments object.
   */
  public function arguments() {
    return $this->arguments;
  }

  /**
   * Gets the command line arguments for the binary.
   *
   * @return string[]
   *   The array of command line arguments.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ::arguments()
   *   instead, using ImagemagickExecArguments methods to manipulate arguments.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2925780
   */
  public function getArguments() {
    @trigger_error('getArguments() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ::arguments() instead, using ImagemagickExecArguments methods to manipulate arguments. See https://www.drupal.org/project/imagemagick/issues/2925780.', E_USER_DEPRECATED);
    return $this->arguments()->getArguments();
  }

  /**
   * Gets the command line arguments string for the binary.
   *
   * Removes any argument used internally within the toolkit.
   *
   * @return string
   *   The string of command line arguments.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::toString() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2925780
   */
  public function getStringForBinary() {
    @trigger_error('getStringForBinary() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::toString() instead. See https://www.drupal.org/project/imagemagick/issues/2925780.', E_USER_DEPRECATED);
    return $this->arguments()->getStringForBinary();
  }

  /**
   * Adds a command line argument.
   *
   * @param string $arg
   *   The command line argument to be added.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::add() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2925780
   */
  public function addArgument($arg) {
    @trigger_error('addArgument() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::add() instead. See https://www.drupal.org/project/imagemagick/issues/2925780.', E_USER_DEPRECATED);
    $this->arguments()->addArgument($arg);
    return $this;
  }

  /**
   * Prepends a command line argument.
   *
   * @param string $arg
   *   The command line argument to be prepended.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::add() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2925780
   */
  public function prependArgument($arg) {
    @trigger_error('prependArgument() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::add() instead. See https://www.drupal.org/project/imagemagick/issues/2925780.', E_USER_DEPRECATED);
    $this->arguments()->prependArgument($arg);
    return $this;
  }

  /**
   * Finds if a command line argument exists.
   *
   * @param string $arg
   *   The command line argument to be found.
   *
   * @return bool
   *   Returns the array key for the argument if it is found in the array,
   *   FALSE otherwise.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::find() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2925780
   */
  public function findArgument($arg) {
    @trigger_error('findArgument() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::find() instead. See https://www.drupal.org/project/imagemagick/issues/2925780.', E_USER_DEPRECATED);
    return $this->arguments()->findArgument($arg);
  }

  /**
   * Removes a command line argument.
   *
   * @param int $index
   *   The index of the command line argument to be removed.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::remove() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2936615
   */
  public function removeArgument($index) {
    @trigger_error('removeArgument() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::remove() instead. See https://www.drupal.org/project/imagemagick/issues/2936615.', E_USER_DEPRECATED);
    $this->arguments()->removeArgument($index);
    return $this;
  }

  /**
   * Resets the command line arguments.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::reset() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2936615
   */
  public function resetArguments() {
    @trigger_error('resetArguments() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::reset() instead. See https://www.drupal.org/project/imagemagick/issues/2936615.', E_USER_DEPRECATED);
    $this->arguments()->resetArguments();
    return $this;
  }

  /**
   * Returns the count of command line arguments.
   *
   * @return $this
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::find() instead, then count the result.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2936615
   */
  public function countArguments() {
    @trigger_error('countArguments() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::find() instead, then count the result. See https://www.drupal.org/project/imagemagick/issues/2936615.', E_USER_DEPRECATED);
    return $this->arguments()->countArguments();
  }

  /**
   * Escapes a string.
   *
   * @param string $arg
   *   The string to escape.
   *
   * @return string
   *   An escaped string for use in the
   *   ImagemagickExecManagerInterface::execute method.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   ImageMagickExecArguments::escape() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2936680
   */
  public function escapeShellArg($arg) {
    @trigger_error('escapeShellArg() is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use ImageMagickExecArguments::escape() instead. See https://www.drupal.org/project/imagemagick/issues/2936680.', E_USER_DEPRECATED);
    return $this->getExecManager()->escapeShellArg($arg);
  }

  /**
   * {@inheritdoc}
   */
  public function save($destination) {
    $this->arguments()->setDestination($destination);
    if ($ret = $this->convert()) {
      // Allow modules to alter the destination file.
      $this->moduleHandler->alter('imagemagick_post_save', $this->arguments);
      // Reset local path to allow saving to other file.
      $this->arguments()->setDestinationLocalPath('');
    }
    return $ret;
  }

  /**
   * {@inheritdoc}
   */
  public function parseFile() {
    if ($this->configFactory->get('imagemagick.settings')->get('use_identify')) {
      return $this->parseFileViaIdentify();
    }
    else {
      return $this->parseFileViaGetImageSize();
    }
  }

  /**
   * Parses the image file using the 'identify' executable.
   *
   * @return bool
   *   TRUE if the file could be found and is an image, FALSE otherwise.
   */
  protected function parseFileViaIdentify() {
    // Get 'imagemagick_identify' metadata for this image. The file metadata
    // plugin will fetch it from the file via the ::identify() method if data
    // is not already available.
    $file_md = $this->fileMetadataManager->uri($this->getSource());
    $data = $file_md->getMetadata('imagemagick_identify');

    // No data, return.
    if (!$data) {
      return FALSE;
    }

    // Sets the local file path to the one retrieved by identify if available.
    if ($source_local_path = $file_md->getMetadata('imagemagick_identify', 'source_local_path')) {
      $this->arguments()->setSourceLocalPath($source_local_path);
    }

    // Process parsed data from the first frame.
    $format = $file_md->getMetadata('imagemagick_identify', 'format');
    if ($this->formatMapper->isFormatEnabled($format)) {
      $this
        ->setWidth((int) $file_md->getMetadata('imagemagick_identify', 'width'))
        ->setHeight((int) $file_md->getMetadata('imagemagick_identify', 'height'))
        ->setExifOrientation($file_md->getMetadata('imagemagick_identify', 'exif_orientation'))
        ->setFrames($file_md->getMetadata('imagemagick_identify', 'frames_count'));
      $this->arguments()
        ->setSourceFormat($format);
      // Only Imagemagick allows to get colorspace and profiles information
      // via 'identify'.
      if ($this->getExecManager()->getPackage() === 'imagemagick') {
        $this->setColorspace($file_md->getMetadata('imagemagick_identify', 'colorspace'));
        $this->setProfiles($file_md->getMetadata('imagemagick_identify', 'profiles'));
      }
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Parses the image file using the file metadata 'getimagesize' plugin.
   *
   * @return bool
   *   TRUE if the file could be found and is an image, FALSE otherwise.
   *
   * @deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use
   *   parseFileViaIdentify() instead.
   *
   * @see https://www.drupal.org/project/imagemagick/issues/2938377
   */
  protected function parseFileViaGetImageSize() {
    @trigger_error('Image file parsing via \'getimagesize\' is deprecated in 8.x-2.3, will be removed in 8.x-3.0. Use parsing via \'identify\' instead. See https://www.drupal.org/project/imagemagick/issues/2938377.', E_USER_DEPRECATED);
    // Allow modules to alter the source file.
    $this->moduleHandler->alter('imagemagick_pre_parse_file', $this->arguments);

    // Get 'getimagesize' metadata for this image.
    $file_md = $this->fileMetadataManager->uri($this->getSource());
    $data = $file_md->getMetadata('getimagesize');

    // No data, return.
    if (!$data) {
      return FALSE;
    }

    // Process parsed data.
    $format = $this->formatMapper->getFormatFromExtension(image_type_to_extension($data[2], FALSE));
    if ($format) {
      $this
        ->setWidth($data[0])
        ->setHeight($data[1])
        // 'getimagesize' cannot provide information on number of frames in an
        // image and EXIF orientation, so set to defaults.
        ->setExifOrientation(static::EXIF_ORIENTATION_NOT_FETCHED)
        ->setFrames(NULL);
      $this->arguments()
        ->setSourceFormat($format);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Calls the convert executable with the specified arguments.
   *
   * @return bool
   *   TRUE if the file could be converted, FALSE otherwise.
   */
  protected function convert() {
    $config = $this->configFactory->get('imagemagick.settings');

    // Ensure sourceLocalPath is prepared.
    $this->ensureSourceLocalPath();

    // Allow modules to alter the command line parameters.
    $command = 'convert';
    $this->moduleHandler->alter('imagemagick_arguments', $this->arguments, $command);

    // Delete any cached file metadata for the destination image file, before
    // creating a new one, and release the URI from the manager so that
    // metadata will not stick in the same request.
    $this->fileMetadataManager->deleteCachedMetadata($this->arguments()->getDestination());
    $this->fileMetadataManager->release($this->arguments()->getDestination());

    // When destination format differs from source format, and source image
    // is multi-frame, convert only the first frame.
    $destination_format = $this->arguments()->getDestinationFormat() ?: $this->arguments()->getSourceFormat();
    if ($this->arguments()->getSourceFormat() !== $destination_format && ($this->getFrames() === NULL || $this->getFrames() > 1)) {
      $this->arguments()->setSourceFrames('[0]');
    }

    // Execute the command and return.
    return $this->getExecManager()->execute($command, $this->arguments) && file_exists($this->arguments()->getDestinationLocalPath());
  }

  /**
   * {@inheritdoc}
   */
  public function getRequirements() {
    $reported_info = [];
    if (stripos(ini_get('disable_functions'), 'proc_open') !== FALSE) {
      // proc_open() is disabled.
      $severity = REQUIREMENT_ERROR;
      $reported_info[] = $this->t("The <a href=':proc_open_url'>proc_open()</a> PHP function is disabled. It must be enabled for the toolkit to work. Edit the <a href=':disable_functions_url'>disable_functions</a> entry in your php.ini file, or consult your hosting provider.", [
        ':proc_open_url' => 'http://php.net/manual/en/function.proc-open.php',
        ':disable_functions_url' => 'http://php.net/manual/en/ini.core.php#ini.disable-functions',
      ]);
    }
    else {
      $status = $this->getExecManager()->checkPath($this->configFactory->get('imagemagick.settings')->get('path_to_binaries'));
      if (!empty($status['errors'])) {
        // Can not execute 'convert'.
        $severity = REQUIREMENT_ERROR;
        foreach ($status['errors'] as $error) {
          $reported_info[] = $error;
        }
        $reported_info[] = $this->t('Go to the <a href=":url">Image toolkit</a> page to configure the toolkit.', [':url' => Url::fromRoute('system.image_toolkit_settings')->toString()]);
      }
      else {
        // No errors, report the version information.
        $severity = REQUIREMENT_INFO;
        $version_info = explode("\n", preg_replace('/\r/', '', Html::escape($status['output'])));
        $value = array_shift($version_info);
        $more_info_available = FALSE;
        foreach ($version_info as $key => $item) {
          if (stripos($item, 'feature') !== FALSE || $key > 3) {
            $more_info_available = TRUE;
            break;

          }
          $reported_info[] = $item;
        }
        if ($more_info_available) {
          $reported_info[] = $this->t('To display more information, go to the <a href=":url">Image toolkit</a> page, and expand the \'Version information\' section.', [':url' => Url::fromRoute('system.image_toolkit_settings')->toString()]);
        }
        $reported_info[] = '';
        $reported_info[] = $this->t("Enabled image file extensions: %extensions", [
          '%extensions' => Unicode::strtolower(implode(', ', static::getSupportedExtensions())),
        ]);
      }
    }
    $requirements = [
      'imagemagick' => [
        'title' => $this->t('ImageMagick'),
        'value' => isset($value) ? $value : NULL,
        'description' => [
          '#markup' => implode('<br />', $reported_info),
        ],
        'severity' => $severity,
      ],
    ];

    // Warn if parsing via 'getimagesize'.
    // @todo remove in 8.x-3.0.
    if ($this->configFactory->getEditable('imagemagick.settings')->get('use_identify') === FALSE) {
      $requirements['imagemagick_getimagesize'] = [
        'title' => $this->t('ImageMagick'),
        'value' => $this->t('Use "identify" to parse image files'),
        'description' => $this->t('The toolkit is set to use the <kbd>getimagesize</kbd> PHP function to parse image files. This functionality will be dropped in the next major release of the Imagemagick module. Go to the <a href=":url">Image toolkit</a> settings page, and ensure that the \'Use "identify"\' flag in the \'Execution options\' tab is selected.', [
          ':url' => Url::fromRoute('system.image_toolkit_settings')->toString(),
        ]),
        'severity' => REQUIREMENT_WARNING,
      ];
    }

    return $requirements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isAvailable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSupportedExtensions() {
    return \Drupal::service('imagemagick.format_mapper')->getEnabledExtensions();
  }

}
