<?php

namespace Drupal\mailchimp_campaign\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the render controller for MailchimpCampaign entities.
 *
 * @ingroup mailchimp_campaign
 */
class MailchimpCampaignViewBuilder extends EntityViewBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $class = parent::createInstance($container, $entity_type);
    $class->dateFormatter = $container->get('date.formatter');
    $class->messenger = $container->get('messenger');
    return $class;
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);

    // Attach campaign JS and CSS.
    $build['#attached']['library'][] = 'mailchimp_campaign/campaign-view';

    // Prepare rendered content.
    /* @var $entity \Drupal\mailchimp_campaign\Entity\MailchimpCampaign */
    $content = $this->renderTemplate($entity->getTemplate());
    $rendered = '';
    foreach ($content as $key => $section) {
      $rendered .= "<h3>$key</h3>" . $section;
    }

    if (!$entity->isInitialized()) {
      $this->messenger->addError($this->t('The campaign could not be found.'));
      return $build;
    }

    // Get the template name.
    $mc_template = mailchimp_campaign_get_template($entity->mc_data->settings->template_id);
    $mc_template_name = isset($mc_template) ? $mc_template->name : '';

    $list_segment_name = 'N/A';

    $list_segments = mailchimp_campaign_get_list_segments($entity->list->id, 'saved');
    if (isset($entity->mc_data->recipients->segment_opts->saved_segment_id)) {
      foreach ($list_segments as $list_segment) {
        if ($list_segment->id == $entity->mc_data->recipients->segment_opts->saved_segment_id) {
          $list_segment_name = $list_segment->name;
        }
      }
    }

    $list_url = Url::fromUri('https://admin.mailchimp.com/lists/dashboard/overview?id=' . $entity->list->id, ['attributes' => ['target' => '_blank']]);
    $archive_url = Url::fromUri($entity->mc_data->archive_url, [
      'attributes' => ['target' => '_blank'],
    ]);
    $send_time = 'N/A';

    if (isset($entity->mc_data->send_time) && $entity->mc_data->send_time) {
      $send_time = $this->dateFormatter
        ->format(strtotime($entity->mc_data->send_time), 'custom', 'F j, Y - g:ia');
    }

    $fields = [
      'title' => [
        'label' => $this->t('Title'),
        'value' => $entity->mc_data->settings->title,
      ],

      'subject' => [
        'label' => $this->t('Subject'),
        'value' => $entity->mc_data->settings->subject_line,
      ],
      'list' => [
        'label' => $this->t('Mailchimp Audience'),
        'value' => Link::fromTextAndUrl($entity->list->name, $list_url)->toString(),
      ],
      'list_segment' => [
        'label' => $this->t('Audience Tags'),
        'value' => $list_segment_name,
      ],
      'from_email' => [
        'label' => $this->t('From Email'),
        'value' => $entity->mc_data->settings->reply_to,
      ],
      'from_name' => [
        'label' => $this->t('From Name'),
        'value' => $entity->mc_data->settings->from_name,
      ],
      'template' => [
        'label' => $this->t('Template'),
        'value' => $mc_template_name,
      ],
      'type' => [
        'label' => $this->t('Audience type'),
        'value' => $entity->mc_data->type,
      ],
      'status' => [
        'label' => $this->t('Status'),
        'value' => $entity->mc_data->status,
      ],
      'emails_sent' => [
        'label' => $this->t('Emails sent'),
        'value' => $entity->mc_data->emails_sent,
      ],
      'send_time' => [
        'label' => $this->t('Send time'),
        'value' => $send_time,
      ],
      'content' => [
        'label' => $this->t('Rendered template HTML (@archive)',
          [
            '@archive' => Link::fromTextAndUrl('View Mailchimp archive', $archive_url)->toString(),
          ]
        ),
        'value' => $rendered,
      ],
    ];

    foreach ($fields as $key => $field) {
      $build[$key] = [
        '#prefix' => "<div class=\"field campaign-{$key}\"><h3 class=\"field-label\">{$field['label']}</h3>",
        '#markup' => "<p>{$field['value']}</p>",
        '#suffix' => '</div>',
      ];
    }

    return $build;
  }

  /**
   * Converts a template into rendered content.
   *
   * @param array $template
   *   Array of template sections.
   *
   * @return array
   *   Array of template content indexed by section ID.
   */
  private function renderTemplate(array $template) {
    $content = [];
    foreach ($template as $key => $part) {
      if (isset($part['format'])) {
        $content[$key] = check_markup($part['value'], $part['format']);
      }
    }

    return $content;
  }

}
