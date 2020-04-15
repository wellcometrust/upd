<?php

namespace Drupal\scheduled_publish\Service;

use DateTime;
use DateTimeZone;
use Drupal\Component\Datetime\Time;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\field\Entity\FieldConfig;
use Drupal\scheduled_publish\Plugin\Field\FieldType\ScheduledPublish;

/**
 * Class ScheduledPublishCron
 *
 * @package Drupal\scheduled_publish\Service
 */
class ScheduledPublishCron {

  /**
   * @var array
   */
  public static $supportedTypes = [
    'node',
    'media',
  ];

  /**
   * @var EntityTypeBundleInfo
   */
  private $entityBundleInfoService;

  /**
   * @var EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * @var EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * @var Time
   */
  private $dateTime;

  /**
   * ScheduledPublishCron constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityBundleInfo
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Component\Datetime\TimeInterface $dateTime
   */
  public function __construct(EntityTypeBundleInfoInterface $entityBundleInfo, EntityFieldManagerInterface $entityFieldManager, EntityTypeManagerInterface $entityTypeManager, TimeInterface $dateTime) {
    $this->entityBundleInfoService = $entityBundleInfo;
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->dateTime = $dateTime;
  }

  /**
   *  Run field updates
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function doUpdate(): void {
    foreach (self::$supportedTypes as $supportedType) {
      $this->doUpdateFor($supportedType);
    }
  }

  /**
   * Run field update for specific entity type
   *
   * @param $entityType
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  private function doUpdateFor($entityType) {
    $bundles = $this->entityBundleInfoService->getBundleInfo($entityType);

    foreach ($bundles as $bundleName => $value) {

      $scheduledFields = $this->getScheduledFields($entityType, $bundleName);
      if (\count($scheduledFields) > 0) {
        foreach ($scheduledFields as $scheduledField) {
          $query = $this->entityTypeManager->getStorage($entityType)
            ->getQuery('AND');
          $query->condition($entityType === 'media' ? 'bundle' : 'type', $bundleName);
          $query->condition($scheduledField, NULL, 'IS NOT NULL');
          $query->accessCheck(FALSE);
          $query->latestRevision();
          $entities = $query->execute();
          foreach ($entities as $entityRevision => $entityId) {
            $entity = $this->entityTypeManager->getStorage($entityType)
              ->loadRevision($entityRevision);
            $this->updateEntityField($entity, $scheduledField);
          }
        }
      }
    }
  }

  /**
   * Returns scheduled publish fields
   *
   * @param string $entityTypeName
   * @param string $bundleName
   *
   * @return array
   */
  private function getScheduledFields(string $entityTypeName, string $bundleName): array {
    $scheduledFields = [];
    $fields = $this->entityFieldManager
      ->getFieldDefinitions($entityTypeName, $bundleName);
    foreach ($fields as $fieldName => $field) {
      /** @var FieldConfig $field */
      if (strpos($fieldName, 'field_') !== FALSE) {
        if ($field->getType() === 'scheduled_publish') {
          $scheduledFields[] = $fieldName;
        }
      }
    }

    return $scheduledFields;
  }

  /**
   * Update scheduled publish fields
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   * @param string $scheduledField
   *
   * @throws \Exception
   */
  private function updateEntityField(ContentEntityBase $entity, string $scheduledField): void {
    /** @var FieldItemList $scheduledEntity */
    $scheduledEntity = $entity->get($scheduledField);
    $scheduledValue = $scheduledEntity->getValue();
    if (empty($scheduledValue)) {
      return;
    }
    $currentModerationState = $entity->get('moderation_state')
      ->getValue()[0]['value'];

    foreach ($scheduledValue as $key => $value) {
      if ($currentModerationState === $value['moderation_state'] ||
        $this->getTimestampFromIso8601($value['value']) <= $this->dateTime->getCurrentTime()) {

        unset($scheduledValue[$key]);
        $this->updateEntity($entity, $value['moderation_state'], $scheduledField, $scheduledValue);
      }
    }
  }

  /**
   * Returns timestamp from ISO-8601 datetime
   *
   * @param string $dateIso8601
   *
   * @return int
   * @throws \Exception
   */
  private function getTimestampFromIso8601(string $dateIso8601): int {
    $datetime = new DateTime($dateIso8601, new DateTimeZone(ScheduledPublish::STORAGE_TIMEZONE));
    $datetime->setTimezone(new \DateTimeZone(drupal_get_user_timezone()));

    return $datetime->getTimestamp();
  }

  /**
   * Updates entity
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   * @param string $moderationState
   * @param string $scheduledPublishField
   * @param $scheduledValue
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateEntity(ContentEntityBase $entity, string $moderationState, string $scheduledPublishField, $scheduledValue): void {
    $entity->set($scheduledPublishField, $scheduledValue);
    $entity->set('moderation_state', $moderationState);
    $entity->save();
  }

}
