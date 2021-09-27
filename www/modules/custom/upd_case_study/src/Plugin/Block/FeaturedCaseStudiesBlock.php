<?php

namespace Drupal\upd_case_study\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\State\StateInterface;

/**
 * Provides a block with 3 latest case studies.
 *
 * @Block(
 *   id = "featured_case_studies",
 *   admin_label = @Translation("Featured case studies"),
 * )
 */
class FeaturedCaseStudiesBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The state.
   *
   * @var Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param Drupal\Core\State\StateInterface $input_state
   *   The request stack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack, StateInterface $input_state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
    $this->state = $input_state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'view_mode' => 'teaser',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#cache'] = ['contexts' => ['url.path']];
    // Only show when no query.
    if (count($this->requestStack->getCurrentRequest()->query) > 0) {
      return $build;
    }
    //$config = $this->getConfiguration();
    //$view_mode = $config['view_mode'];
    $entity_ids = [];
    for ($i = 1; $i <= 3; $i++) {
      $entity_ids[$i] = $this->state->get('featured_case_study_' . $i);
      if ($entity_ids[$i] == NULL) {
        return $build;
      }
    }
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($entity_ids);
    foreach ($nodes as $node) {
      $build['#items'][] = $this->entityTypeManager->getViewBuilder('node')->view($node, 'teaser');
    }
    $build['#grid'] = 3;
    $build['#theme'] = 'upd_grid_list';
    $build['#prefix'] = '<header class="search-results__header">';
    $build['#suffix'] = '</header>';
    return $build;
  }

}
