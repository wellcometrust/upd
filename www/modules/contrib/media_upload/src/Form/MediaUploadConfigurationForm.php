<?php

namespace Drupal\media_upload\Form;

use Drupal\Component\Utility\Bytes;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Contribute form.
 */
class MediaUploadConfigurationForm extends ConfigFormBase {
  const COMPLETE_FILE_NAME = 0;
  const FILE_NAME          = 1;
  const TAG_NAME           = 2;
  const EXT_NAME           = 3;
  private $mediaBundle     = [
    'audio'    => FALSE,
    'video'    => FALSE,
    'file'     => FALSE,
    'image'    => FALSE,
  ];
  private $videoBundles    = [];
  private $audioBundles    = [];
  private $documentBundles = [];
  private $imageBundles    = [];
  private $entityTypeManager;

  /**
   * Creates an instance of this form, with injected dependencies.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container interface.
   *
   * @return static
   *   Instance of this form.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * MediaUploadConfigurationForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;

    $videoBundles = $this->entityTypeManager
      ->getStorage('media_type')
      ->loadByProperties(['source' => 'video']);
    foreach ($videoBundles as $item) {
      /** @var \Drupal\media\Entity\MediaType $item */
      $this->videoBundles[$item->id()] = "{$item->label()} ({$item->id()})";
    }

    $audioBundles = $this->entityTypeManager
      ->getStorage('media_type')
      ->loadByProperties(['source' => 'audio']);
    foreach ($audioBundles as $item) {
      /** @var \Drupal\media\Entity\MediaType $item */
      $this->audioBundles[$item->id()] = "{$item->label()} ({$item->id()})";
    }

    $documentBundles = $this->entityTypeManager
      ->getStorage('media_type')
      ->loadByProperties(['source' => 'file']);
    foreach ($documentBundles as $item) {
      /** @var \Drupal\media\Entity\MediaType $item */
      $this->documentBundles[$item->id()] = "{$item->label()} ({$item->id()})";
    }

    $imageBundles = $this->entityTypeManager
      ->getStorage('media_type')
      ->loadByProperties(['source' => 'image']);
    foreach ($imageBundles as $item) {
      /** @var \Drupal\media\Entity\MediaType $item */
      $this->imageBundles[$item->id()] = "{$item->label()} ({$item->id()})";
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'mediaupload_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    Request $request = NULL
  ) {

    $config = $this->config('media_upload.settings');
    $form['bundles'] = [
      '#type'          => 'checkboxes',
      '#title'         => $this->t('Accepted media types'),
      '#options'       => [
        'video'    => $this->t('Video'),
        'image'    => $this->t('Image'),
        'document' => $this->t('File (Document)'),
        'audio'    => $this->t('Audio'),
      ],
      '#default_value' => $config->get('bundles') ? $config->get('bundles') : [],
    ];
    $form['total_size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Max total size'),
      '#description'   => $this->t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size. Keep blank or set to 0 to disable limit.'),
      '#default_value' => $config->get('total_size'),
    ];
    $form['single_size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Max single file size'),
      '#description'   => $this->t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size. Keep blank or set to 0 to disable limit.'),
      '#default_value' => $config->get('single_size'),
    ];

    $form['video'] = [
      '#type'   => 'fieldset',
      '#title'  => $this->t('Video parameters'),
      '#states' => [
        'invisible' => [
          ':input[name="bundles[video]"]' => ['checked' => FALSE],
        ],
        'disabled'  => [
          ':input[name="bundles[video]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['video']['video_bun'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Video media bundle'),
      '#options'       => $this->videoBundles,
      '#default_value' => $config->get('video_bundle'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[video]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[video]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateFields',
        'event'    => 'change',
      ],
    ];
    $form['video']['video_fie'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Video field'),
      '#options'       => $this->initFields($config->get('video_bundle')),
      '#default_value' => $config->get('video_field'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[video]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[video]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateValues',
        'event'    => 'change',
      ],
    ];
    $form['video']['video_ext'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Allowed video extensions'),
      '#default_value' => $config->get('video_ext'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];
    $form['video']['video_size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Max video size'),
      '#default_value' => $config->get('video_size'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];

    $form['image'] = [
      '#type'   => 'fieldset',
      '#title'  => $this->t('Image parameters'),
      '#states' => [
        'invisible' => [
          ':input[name="bundles[image]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['image']['image_bun'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Image media bundle'),
      '#options'       => $this->imageBundles,
      '#default_value' => $config->get('image_bundle'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[image]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[image]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateFields',
        'event'    => 'change',
      ],
    ];
    $form['image']['image_fie'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Image field'),
      '#options'       => $this->initFields($config->get('image_bundle')),
      '#default_value' => $config->get('image_field'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[image]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[image]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateValues',
        'event'    => 'change',
      ],
    ];
    $form['image']['image_ext'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Allowed image extensions'),
      '#default_value' => $config->get('image_ext'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];
    $form['image']['image_size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Max image size'),
      '#default_value' => $config->get('image_size'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];

    $form['document'] = [
      '#type'   => 'fieldset',
      '#title'  => $this->t('Document parameters'),
      '#states' => [
        'invisible' => [
          ':input[name="bundles[document]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['document']['document_bun'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Document media bundle'),
      '#options'       => $this->documentBundles,
      '#default_value' => $config->get('document_bundle'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[document]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[document]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateFields',
        'event'    => 'change',
      ],
    ];
    $form['document']['document_fie'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Document field'),
      '#options'       => $this->initFields($config->get('document_bundle')),
      '#default_value' => $config->get('document_field'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[document]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[document]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateValues',
        'event'    => 'change',
      ],
    ];
    $form['document']['document_ext'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Allowed document extensions'),
      '#default_value' => $config->get('document_ext'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];
    $form['document']['document_size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Max document size'),
      '#default_value' => $config->get('document_size'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];

    $form['audio'] = [
      '#type'   => 'fieldset',
      '#title'  => $this->t('Audio parameters'),
      '#states' => [
        'invisible' => [
          ':input[name="bundles[audio]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['audio']['audio_bun'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Audio media bundle'),
      '#options'       => $this->audioBundles,
      '#default_value' => $config->get('audio_bundle'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[audio]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[audio]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateFields',
        'event'    => 'change',
      ],
    ];
    $form['audio']['audio_fie'] = [
      '#type'          => 'select',
      '#validated'     => TRUE,
      '#title'         => $this->t('Audio field'),
      '#options'       => $this->initFields($config->get('audio_bundle')),
      '#default_value' => $config->get('audio_field'),
      '#states'        => [
        'required' => [
          ':input[name="bundles[audio]"]' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input[name="bundles[audio]"]' => ['checked' => FALSE],
        ],
      ],
      '#ajax'          => [
        'callback' => 'Drupal\media_upload\Form\MediaUploadConfigurationForm::populateValues',
        'event'    => 'change',
      ],
    ];
    $form['audio']['audio_ext'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Allowed audio extensions'),
      '#default_value' => $config->get('audio_ext'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];
    $form['audio']['audio_size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Max audio size'),
      '#default_value' => $config->get('audio_size'),
      '#attributes'    => ['readonly' => 'readonly'],
    ];

    $this->checkMediaBundles($form);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->isInt('total_size', $form_state);
    $this->isInt('single_size', $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('media_upload.settings');

    $config->set('bundles', $values['bundles']);
    $config->set('total_size', $values['total_size']);
    $config->set('single_size', $values['single_size']);

    if (!empty($values['bundles']['video'])) {
      $config->set('video_bundle', $values['video_bun']);
      $config->set('video_field', $values['video_fie']);
      $config->set('video_ext', preg_replace('/,|,?\s+/', ' ', $values['video_ext']));
      $config->set('video_size', $values['video_size']);
    }

    if (!empty($values['bundles']['image'])) {
      $config->set('image_bundle', $values['image_bun']);
      $config->set('image_field', $values['image_fie']);
      $config->set('image_ext', preg_replace('/,|,?\s+/', ' ', $values['image_ext']));
      $config->set('image_size', $values['image_size']);
    }

    if (!empty($values['bundles']['document'])) {
      $config->set('document_bundle', $values['document_bun']);
      $config->set('document_field', $values['document_fie']);
      $config->set('document_ext', preg_replace('/,|,?\s+/', ' ', $values['document_ext']));
      $config->set('document_size', $values['document_size']);
    }

    if (!empty($values['bundles']['audio'])) {
      $config->set('audio_bundle', $values['audio_bun']);
      $config->set('audio_field', $values['audio_fie']);
      $config->set('audio_ext', preg_replace('/,|,?\s+/', ' ', $values['audio_ext']));
      $config->set('audio_size', $values['audio_size']);
    }

    $config->save();
    drupal_set_message($this->t('Configuration done.'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {

    return ['media_upload.settings'];
  }

  /**
   * Checks if value of the given field is an integer.
   *
   * @param string $fieldName
   *   Field name.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  private function isInt($fieldName, FormStateInterface $form_state) {
    $val = $form_state->getValue($fieldName);
    if (empty($val)) {
      $val = 0;
    }
    else {
      $val = Bytes::toInt($val);
    }
    if (!is_numeric($val)) {
      $form_state->setErrorByName($fieldName, $this->t('The size must be a positive integer value'));
    }
    else {
      if (intval($val) < 0) {
        $form_state->setErrorByName($fieldName, $this->t('The size must be a positive integer value'));
      }
    }
  }

  /**
   * Checks acceptable media bundles to receive uploads.
   *
   * @param array $form
   *   Form array.
   */
  private function checkMediaBundles(array &$form) {
    foreach ($this->mediaBundle as $item => $value) {
      if (empty(\Drupal::entityTypeManager()
        ->getStorage('media_type')
        ->loadByProperties(['source' => $item]))
      ) {
        drupal_set_message($this->t("You don't seem to have media of the @item type. You should <a href=':link'>create one</a>", [
          '@item' => $item,
          ':link' => Url::fromRoute('entity.media_type.add_form')->toString(),
        ]), 'warning');
        $form['bundles'][$item]['#disabled'] = TRUE;
      }
    }
  }

  /**
   * Populate acceptable fields in a given media bundle to receive source file.
   *
   * @param string $bundle
   *   Bundle name.
   *
   * @return array
   *   Return an array containing all acceptable fields.
   */
  private function initFields($bundle = NULL) {
    if (!$bundle) {
      return [];
    }
    $fields = \Drupal::entityManager()
      ->getFieldDefinitions('media', $bundle);
    $validFields = [];
    foreach ($fields as $field) {
      if (get_class($field) === 'Drupal\field\Entity\FieldConfig') {
        $validFields[$field->getName()] = $field->getLabel() . ' (' . $field->getName() . ')';
      }
    }

    return $validFields;
  }

  /**
   * This method is meant to be called by Drupal Ajax Handler.
   *
   * Populate field select input regarding to the selected Media Bundle.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return the AjaxResponse object.
   */
  public static function populateFields(
    array &$form,
    FormStateInterface $form_state
  ) {
    $fields = \Drupal::entityManager()
      ->getFieldDefinitions('media', $form_state->getTriggeringElement()['#value']);
    $validFields = [];
    foreach ($fields as $field) {
      if (self::isFileField($field)) {
        $validFields[] = [
          'value' => $field->getName(),
          'text'  => $field->getLabel(),
        ];
      }
    }
    $selector = preg_replace('/bun.*/', 'fie', $form_state->getTriggeringElement()['#id']);
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand("#$selector", 'empty'));
    $response->addCommand(new InvokeCommand("#$selector", 'html', [
      "<option value=\"\" selected=\"selected\">- Select -</option>",
    ]));
    foreach ($validFields as $item) {
      $response->addCommand(new InvokeCommand("#$selector", 'append', [
        "<option value='{$item['value']}'>{$item['text']} ({$item['value']})</option>",
      ]));
    }
    return $response;
  }

  /**
   * This method is meant to be called by Drupal Ajax Handler.
   *
   * Populate size and extensions inputs regarding to the selected field.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return the AjaxResponse object.
   */
  public static function populateValues(
    array &$form,
    FormStateInterface $form_state
  ) {
    $bundleType = str_replace('_fie', '', $form_state->getTriggeringElement()['#name']);
    $field = FieldConfig::loadByName('media', $form[$bundleType][$bundleType . '_bun']['#value'], $form_state->getTriggeringElement()['#value']);

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand("#edit-$bundleType-ext", 'val', [$field->getSetting('file_extensions')]));
    $response->addCommand(new InvokeCommand("#edit-$bundleType-size", 'val', [$field->getSetting('max_filesize')]));

    return $response;
  }

  /**
   * Check is field conf entity represent UI configurable file reference field.
   *
   * @param object $field
   *   \Drupal\field\Entity\FieldConfig or
   *   Drupal\Core\Field\BaseFieldDefinition object.
   *
   * @return bool
   *   True if field configuration entity represent a file reference field.
   */
  protected static function isFileField($field) {
    if (is_a($field, 'Drupal\field\Entity\FieldConfig')) {
      $field_definitions = \Drupal::service('plugin.manager.field.field_type')
        ->getDefinitions();
      /* @var \Drupal\field\Entity\FieldConfig $field */
      $field_class = $field_definitions[$field->getType()]['class'];
      if (is_subclass_of($field_class, 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem')) {
        $storage = $field->getFieldStorageDefinition();
        $target_type = $storage->getSetting('target_type');
        if ($target_type == 'file') {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
