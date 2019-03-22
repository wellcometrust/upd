<?php

namespace Drupal\node_weight\Plugin\Derivative;

use Drupal\node\Entity\NodeType;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derivative class that provides the menu links for the Node Weights.
 */
class NodeWeightMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a NodeMenuLink instance.
   *
   * @param int $base_plugin_id
   *   Base plugin id.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $config = \Drupal::config('node_weight.settings');
    $node_type_objects = $config->get('node_weight.checked_node_types') ?: [];

    foreach ($node_type_objects as $nto) {
      $node_type = NodeType::load($nto);
      $links[$nto] = [
        'title' => $node_type->label(),
        'route_name' => 'node_weight.order',
        'route_parameters' => ['node_type' => $nto],
      ] + $base_plugin_definition;
    }

    return $links;
  }

}
