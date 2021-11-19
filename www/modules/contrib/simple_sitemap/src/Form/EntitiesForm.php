<?php

namespace Drupal\simple_sitemap\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\simple_sitemap\Manager\EntityManager;
use Drupal\simple_sitemap\Entity\SimpleSitemap;
use Drupal\simple_sitemap\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_sitemap\Manager\Generator;
use Drupal\simple_sitemap\Entity\EntityHelper;

/**
 * Provides form to manage entity settings.
 */
class EntitiesForm extends SimpleSitemapFormBase {

  /**
   * Helper class for working with entities.
   *
   * @var \Drupal\simple_sitemap\Entity\EntityHelper
   */
  protected $entityHelper;

  /**
   * The simple_sitemap.entity_manager service.
   *
   * @var \Drupal\simple_sitemap\Manager\EntityManager
   */
  protected $sitemapEntities;

  /**
   * EntitiesForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\simple_sitemap\Manager\Generator $generator
   *   The sitemap generator service.
   * @param \Drupal\simple_sitemap\Settings $settings
   *   The simple_sitemap.settings service.
   * @param \Drupal\simple_sitemap\Form\FormHelper $form_helper
   *   Simple XML Sitemap form helper.
   * @param \Drupal\simple_sitemap\Entity\EntityHelper $entity_helper
   *   Helper class for working with entities.
   * @param \Drupal\simple_sitemap\Manager\EntityManager $sitemap_entities
   *   The simple_sitemap.entity_manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Generator $generator,
    Settings $settings,
    FormHelper $form_helper,
    EntityHelper $entity_helper,
    EntityManager $sitemap_entities
  ) {
    parent::__construct(
      $config_factory,
      $generator,
      $settings,
      $form_helper
    );
    $this->entityHelper = $entity_helper;
    $this->sitemapEntities = $sitemap_entities;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.settings'),
      $container->get('simple_sitemap.form_helper'),
      $container->get('simple_sitemap.entity_helper'),
      $container->get('simple_sitemap.entity_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'simple_sitemap_entities_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['simple_sitemap_entities']['#prefix'] = FormHelper::getDonationText();

    $form['simple_sitemap_entities']['entities'] = [
      '#title' => $this->t('Sitemap entities'),
      '#type' => 'fieldset',
      '#markup' => '<div class="description">' . $this->t('Simple XML Sitemap settings will be added only to entity forms of entity types enabled here. For all entity types featuring bundles (e.g. <em>node</em>) sitemap settings have to be set on their bundle pages (e.g. <em>page</em>).') . '</div>',
    ];

    $form['#attached']['library'][] = 'simple_sitemap/sitemapEntities';
    $form['#attached']['drupalSettings']['simple_sitemap'] = [
      'all_entities' => [],
      'atomic_entities' => [],
    ];

    $all_bundle_settings = $this->generator->setVariants()->entityManager()->getAllBundleSettings();
    $indexed_bundles = [];
    foreach ($all_bundle_settings as $variant => $entity_types) {
      foreach ($entity_types as $entity_type_name => $bundles) {
        foreach ($bundles as $bundle_name => $bundle_settings) {
          if ($bundle_settings['index']) {
            $indexed_bundles[$entity_type_name][$bundle_name]['variants'][] = SimpleSitemap::load($variant)->label();
            $indexed_bundles[$entity_type_name][$bundle_name]['bundle_label'] = $this->entityHelper->getBundleLabel($entity_type_name, $bundle_name);
          }
        }
      }
    }

    $entity_type_labels = [];
    foreach ($this->entityHelper->getSupportedEntityTypes() as $entity_type_id => $entity_type) {
      $entity_type_labels[$entity_type_id] = $entity_type->getLabel() ?: $entity_type_id;
    }
    asort($entity_type_labels);

    foreach ($entity_type_labels as $entity_type_id => $entity_type_label) {
      $enabled_entity_type = $this->generator->entityManager()->entityTypeIsEnabled($entity_type_id);
      $atomic_entity_type = $this->entityHelper->entityTypeIsAtomic($entity_type_id);
      $css_entity_type_id = str_replace('_', '-', $entity_type_id);

      $form['simple_sitemap_entities']['entities'][$entity_type_id] = [
        '#type' => 'details',
        '#title' => $entity_type_label,
        '#open' => $enabled_entity_type,
      ];

      $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable @entity_type_label <em>(@entity_type_id)</em> support', [
          '@entity_type_label' => $entity_type_label,
          '@entity_type_id' => $entity_type_id,
        ]),
        '#description' => $atomic_entity_type
        ? $this->t('Sitemap settings for the entity type <em>@entity_type_label</em> can be set below and overridden on its entity pages.', ['@entity_type_label' => $entity_type_label])
        : $this->t('Sitemap settings for the entity type <em>@entity_type_label</em> can be set on its bundle pages and overridden on its entity pages.', ['@entity_type_label' => $entity_type_label]),
        '#default_value' => $enabled_entity_type,
      ];

      if ($form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_enabled']['#default_value']) {

        $indexed_bundles_string = '';
        if (isset($indexed_bundles[$entity_type_id])) {
          foreach ($indexed_bundles[$entity_type_id] as $bundle_data) {
            $indexed_bundles_string .= '<br><em>' . $bundle_data['bundle_label'] . '</em> <span class="description">(' . $this->t('sitemaps') . ': <em>' . implode(', ', $bundle_data['variants']) . '</em>)</span>';
          }
        }

        $bundle_info = '';
        if (!$atomic_entity_type) {
          $bundle_info .= '<div id="indexed-bundles-' . $css_entity_type_id . '">'
            . (!empty($indexed_bundles_string)
              ? $this->t("<em>@entity_type_label</em> bundles set to be indexed:", ['@entity_type_label' => $entity_type_label]) . ' ' . $indexed_bundles_string
              : $this->t('No <em>@entity_type_label</em> bundles are set to be indexed yet.', ['@entity_type_label' => $entity_type_label]))
            . '</div>';
        }

        if (!empty($indexed_bundles_string)) {
          $bundle_info .= '<div id="warning-' . $css_entity_type_id . '">'
            . ($atomic_entity_type
              ? $this->t("<strong>Warning:</strong> This entity type's sitemap settings including per-entity overrides will be deleted after hitting <em>Save</em>.")
              : $this->t("<strong>Warning:</strong> The sitemap settings and any per-entity overrides will be deleted for the following bundles:" . $indexed_bundles_string))
            . '</div>';
        }

        $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_enabled']['#suffix'] = $bundle_info;
      }

      $form['#attached']['drupalSettings']['simple_sitemap']['all_entities'][] = $css_entity_type_id;

      if ($atomic_entity_type) {
        $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_settings']['#prefix'] = '<div id="indexed-bundles-' . $css_entity_type_id . '">';
        $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_settings']['#suffix'] = '</div>';

        $this->formHelper
          ->cleanUpFormInfo()
          ->setEntityCategory('bundle')
          ->setEntityTypeId($entity_type_id)
          ->setBundleName($entity_type_id)
          ->negotiateSettings()
          ->displayEntitySettings(
            $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_settings']
          );
      }
    }

    $this->formHelper->displayRegenerateNow($form['simple_sitemap_entities']['entities']);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    foreach ($values as $field_name => $value) {
      if (substr($field_name, -strlen('_enabled')) === '_enabled') {
        $entity_type_id = substr($field_name, 0, -8);
        if ($value) {
          $this->generator->entityManager()->enableEntityType($entity_type_id);
          if ($this->entityHelper->entityTypeIsAtomic($entity_type_id)) {
            foreach (SimpleSitemap::loadMultiple() as $variant => $sitemap) {
              if (isset($values['index_' . $variant . '_' . $entity_type_id . '_settings'])) {
                $this->generator
                  ->setVariants($variant)
                  ->entityManager()->setBundleSettings($entity_type_id, $entity_type_id, [
                    'index' => (bool) $values['index_' . $variant . '_' . $entity_type_id . '_settings'],
                    'priority' => $values['priority_' . $variant . '_' . $entity_type_id . '_settings'],
                    'changefreq' => $values['changefreq_' . $variant . '_' . $entity_type_id . '_settings'],
                    'include_images' => (bool) $values['include_images_' . $variant . '_' . $entity_type_id . '_settings'],
                  ]);
              }
            }
          }
        }
        else {
          $this->generator->entityManager()->disableEntityType($entity_type_id);
        }
      }
    }
    parent::submitForm($form, $form_state);

    // Regenerate sitemaps according to user setting.
    if ($form_state->getValue('simple_sitemap_regenerate_now')) {
      $this->generator
        ->setVariants()
        ->rebuildQueue()
        ->generate();
    }
  }

}
