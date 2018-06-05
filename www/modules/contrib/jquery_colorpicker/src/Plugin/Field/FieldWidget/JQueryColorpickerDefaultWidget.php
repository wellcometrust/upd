<?php

namespace Drupal\jquery_colorpicker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\jquery_colorpicker\Service\JQueryColorpickerServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The default jquery_colorpicker field widget.
 *
 * @FieldWidget(
 *   id = "jquery_colorpicker",
 *   label = @Translation("jQuery Colorpicker"),
 *   field_types = {
 *      "jquery_colorpicker"
 *   }
 * )
 */
class JQueryColorpickerDefaultWidget extends WidgetBase implements WidgetInterface, ContainerFactoryPluginInterface {

  /**
   * The JQuery Colorpicker service.
   *
   * @var \Drupal\jquery_colorpicker\Service\JQueryColorpickerServiceInterface
   */
  protected $JQueryColorpickerService;

  /**
   * Constructs a JQueryColorpickerDefaultWidget object.
   *
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array $settings
   *   The field settings.
   * @param array $third_party_settings
   *   Third party field settings.
   * @param Drupal\jquery_colorpicker\Service\JQueryColorpickerServiceInterface $jQueryColorpickerService
   *   The jQuery Colorpicker service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, JQueryColorpickerServiceInterface $jQueryColorpickerService) {

    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->JQueryColorpickerService = $jQueryColorpickerService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('jquery_colorpicker.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'color' => 'FFFFFF',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['color'] = [
      '#type' => 'textfield',
      '#field_prefix' => '#',
      '#title' => t('Color'),
      '#default_value' => $this->getSetting('color'),
      '#required' => TRUE,
      '#element_validate' => [
        [$this, 'settingsFormValidate'],
      ],
    ];

    return $element;
  }

  /**
   * Validate the submitted settings.
   */
  public function settingsFormValidate($element, FormStateInterface $form_state) {

    $color = $form_state->getValue($element['#parents']);

    $results = $this->JQueryColorpickerService->validateColor($color);

    $form_state->setValueForElement($element, $results['color']);
    if (isset($results['error'])) {
      $form_state->setError($element, $results['error']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = [];

    $summary[] = t('Default Color: @color', ['@color' => '#' . $this->getSetting('color')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $element['value'] = $element + [
      '#type' => 'jquery_colorpicker',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : 'FFFFFF',
      '#description' => $element['#description'],
      '#cardinality' => $this->fieldDefinition->getFieldStorageDefinition()->getCardinality(),
    ];

    return $element;
  }

}
