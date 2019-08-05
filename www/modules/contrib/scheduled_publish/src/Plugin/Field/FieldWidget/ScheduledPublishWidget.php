<?php

namespace Drupal\scheduled_publish\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\scheduled_publish\Plugin\Field\FieldType\ScheduledPublish;

/**
 * Plugin implementation of the 'scheduled_publish_widget' widget.
 *
 * @FieldWidget(
 *   id = "scheduled_publish",
 *   label = @Translation("Scheduled Publish"),
 *   field_types = {
 *     "scheduled_publish"
 *   }
 * )
 */
class ScheduledPublishWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {

    if ($form_state->getBuildInfo()['base_form_id'] !== 'field_config_form') {
      $element['moderation_state'] = [
        '#type' => 'select',
        '#title' => $this->t('Moderation state'),
        '#description' => $this->t('Set to published state'),
        '#size' => 5,
        '#default_value' => 'draft',
        '#weight' => '0',
        '#required' => $element['#required'],
      ];
    }

    $element['value'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Scheduled date'),
      '#description' => $this->t('The datetime of the scheduled publish'),
      '#weight' => '0',
      '#default_value' => NULL,
      '#date_increment' => 1,
      '#date_timezone' => drupal_get_user_timezone(),
      '#required' => $element['#required'],
    ];

    if ($items[$delta]->moderation_state) {
      $state = $items[$delta]->moderation_state;
      $element['moderation_state']['#default_value'] = $state;
    }

    if ($items[$delta]->date) {
      $date = $items[$delta]->date;
      $date->setTimezone(new \DateTimeZone($element['value']['#date_timezone']));
      $element['value']['#default_value'] = $this->createDefaultValue($date, $element['value']['#date_timezone']);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state): array {
    foreach ($values as &$item) {
      if (!empty($item['value']) && $item['value'] instanceof DrupalDateTime) {
        $date = $item['value'];
        $format = ScheduledPublish::DATETIME_STORAGE_FORMAT;
        // Adjust the date for storage.
        $date->setTimezone(new \DateTimezone(ScheduledPublish::STORAGE_TIMEZONE));
        $item['value'] = $date->format($format);
      }
    }

    return $values;
  }

  /**
   * Creates default datetime object
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   * @param string $timezone
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  private function createDefaultValue(DrupalDateTime $date, string $timezone): DrupalDateTime {
    $date->setTimezone(new \DateTimeZone($timezone));

    return $date;
  }

}
