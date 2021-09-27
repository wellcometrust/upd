<?php

namespace Drupal\mailchimp_campaign\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\mailchimp_campaign\Entity\MailchimpCampaign;
use Mailchimp\MailchimpAPIException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mailchimp Campaign controller.
 */
class MailchimpCampaignController extends ControllerBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Initializes a MailchimpCampaignController.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(Request $request, DateFormatterInterface $date_formatter, EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger, LoggerInterface $logger) {
    $this->request = $request;
    $this->dateFormatter = $date_formatter;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('request_stack')->getCurrentRequest(),
          $container->get('date.formatter'),
          $container->get('entity_type.manager'),
          $container->get('messenger'),
          $container->get('logger.factory')->get('mailchimp_campaign')
        );
  }

  /**
   * {@inheritdoc}
   */
  public function overview() {
    $content = [];

    $content['description'] = [
      '#markup' => $this->t('<p>Add a campaign to use entities as campaign content.</p>
 <p>Importing campaigns from Mailchimp is not possible.</p>'),
    ];

    $content['campaigns_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Subject'),
        $this->t('Status'),
        $this->t('Mailchimp Audience'),
        $this->t('Mailchimp Template'),
        $this->t('Created'),
        $this->t('Actions'),
      ],
      '#empty' => '',
    ];

    try {
      $campaigns = mailchimp_campaign_load_multiple();
      $templates = mailchimp_campaign_list_templates();
    }
    catch (MailchimpAPIException $e) {
      $this->messenger()->addError($this->t('Unable to fetch Mailchimp campaign data, caught API exception: @error', [
        '@error' => $e->getMessage(),
      ]));
      return $content;
    }

    /* @var $campaign \Drupal\mailchimp_campaign\Entity\MailchimpCampaign */
    foreach ($campaigns as $campaign) {
      if (!$campaign->isInitialized()) {
        continue;
      }
      // Ensure the associated list/audience still exists.
      if (!isset($campaign->list) || !$campaign->list) {
        continue;
      }

      $campaign_id = $campaign->getMcCampaignId();

      $archive_url = Url::fromUri($campaign->mc_data->archive_url);
      $campaign_url = Url::fromRoute('entity.mailchimp_campaign.view', ['mailchimp_campaign' => $campaign_id]);
      $list_url = Url::fromUri('https://admin.mailchimp.com/lists/dashboard/overview?id=' . $campaign->list->id, ['attributes' => ['target' => '_blank']]);
      $send_url = Url::fromRoute('entity.mailchimp_campaign.send', ['mailchimp_campaign' => $campaign_id]);

      if ($campaign->mc_data->status === "save") {
        $send_link = Link::fromTextAndUrl($this->t("Send"), $send_url)->toString();
      }
      // "Sent" campaigns were not being cached, so we needed to reload to get
      // the latest status.
      elseif ($campaign->mc_data->status === "sending") {
        $campaigns = mailchimp_campaign_load_multiple([$campaign_id], TRUE);
        $campaign = $campaigns[$campaign_id];
        $send_link = $this->t("Sent");
      }
      else {
        $send_link = $this->t("Sent");
      }

      $actions = [
        Link::fromTextAndUrl(('View Archive'), $archive_url)->toString(),
        Link::fromTextAndUrl(('View'), $campaign_url)->toString(),
        $send_link,
      ];

      $content['campaigns_table'][$campaign_id]['title'] = [
        '#markup' => Link::fromTextAndUrl($campaign->mc_data->settings->title, $campaign_url)->toString(),
      ];

      $content['campaigns_table'][$campaign_id]['subject'] = [
        '#markup' => $campaign->mc_data->settings->subject_line,
      ];

      $content['campaigns_table'][$campaign_id]['status'] = [
        '#markup' => $campaign->mc_data->status,
      ];

      $content['campaigns_table'][$campaign_id]['list'] = [
        '#markup' => Link::fromTextAndUrl($campaign->list->name, $list_url)->toString(),
      ];

      if (empty($campaign->mc_data->settings->template_id)) {
        $content['campaigns_table'][$campaign_id]['template'] = [
          '#markup' => "-- none --",
        ];
      }
      else {
        $template_url = Url::fromUri('https://admin.mailchimp.com/templates/edit?id=' . $campaign->mc_data->settings->template_id, ['attributes' => ['target' => '_blank']]);
        $category = FALSE;
        // Templates are grouped into categories, so we go hunting for our
        // template ID in each category.
        $template_category = [];
        foreach ($templates as $category_name => $template_category) {
          if (isset($template_category[$campaign->mc_data->settings->template_id])) {
            $category = $category_name;
            break;
          }
        }
        if ($category) {
          $content['campaigns_table'][$campaign_id]['template'] = [
            '#markup' => Link::fromTextAndUrl($template_category[$campaign->mc_data->settings->template_id]->name, $template_url)->toString(),
          ];
        }
        else {
          $content['campaigns_table'][$campaign_id]['template'] = [
            '#markup' => $this->t('-- template %template_url not found --',
            [
              '%template_url' => Link::fromTextAndUrl($campaign->mc_data->settings->template_id, $template_url)->toString(),
            ]),
          ];
        }
      }
      $content['campaigns_table'][$campaign_id]['created'] = [
        '#markup' => $this->dateFormatter->format(strtotime($campaign->mc_data->create_time), 'custom', 'F j, Y - g:ia'),
      ];

      $content['campaigns_table'][$campaign_id]['actions'] = [
        '#markup' => implode(' | ', $actions),
      ];
    }

    $mailchimp_campaigns_url = Url::fromUri('https://admin.mailchimp.com/campaigns', ['attributes' => ['target' => '_blank']]);

    $content['mailchimp_list_link'] = [
      '#title' => $this->t('Go to Mailchimp Campaigns'),
      '#type' => 'link',
      '#url' => $mailchimp_campaigns_url,
    ];

    return $content;
  }

  /**
   * View a Mailchimp campaign.
   *
   * @param \Drupal\mailchimp_campaign\Entity\MailchimpCampaign $mailchimp_campaign
   *   The Mailchimp campaign to view.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function view(MailchimpCampaign $mailchimp_campaign) {
    $view_builder = $this->entityTypeManager->getViewBuilder('mailchimp_campaign');

    $content = $view_builder->view($mailchimp_campaign);

    return $content;
  }

  /**
   * View a Mailchimp campaign stats.
   *
   * @param \Drupal\mailchimp_campaign\Entity\MailchimpCampaign $mailchimp_campaign
   *   The Mailchimp campaign to view stats for.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function stats(MailchimpCampaign $mailchimp_campaign) {
    $content = [];

    /* @var \Mailchimp\MailchimpReports $mc_reports */
    $mc_reports = mailchimp_get_api_object('MailchimpReports');

    try {
      if (!$mc_reports) {
        throw new MailchimpAPIException('Cannot get campaign stats without Mailchimp API. Check API key has been entered.');
      }

      $response = $mc_reports->getCampaignSummary($mailchimp_campaign->getMcCampaignId());
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
      $this->logger
        ->error('An error occurred getting report data from Mailchimp: {message}', [
          'message' => $e->getMessage(),
        ]);
    }

    if (!empty($response)) {
      // Attach stats JS.
      $content['#attached']['library'][] = 'mailchimp_campaign/google-jsapi';
      $content['#attached']['library'][] = 'mailchimp_campaign/campaign-stats';

      // Time series chart data.
      $content['#attached']['drupalSettings']['mailchimp_campaign'] = [
        'stats' => [],
      ];

      foreach ($response->timeseries as $series) {
        $content['#attached']['drupalSettings']['mailchimp_campaign']['stats'][] = [
          'timestamp' => $series->timestamp,
          'emails_sent' => isset($series->emails_sent) ? $series->emails_sent : 0,
          'unique_opens' => $series->unique_opens,
          'recipients_click' => isset($series->recipients_click) ? $series->recipients_click : 0,
        ];
      }

      $content['charts'] = [
        '#prefix' => '<h2>' . $this->t('Hourly stats for the first 24 hours of the campaign') . '</h2>',
        '#markup' => '<div id="mailchimp-campaign-chart"></div>',
      ];

      $content['metrics_table'] = [
        '#type' => 'table',
        '#header' => [$this->t('Key'), $this->t('Value')],
        '#empty' => '',
        '#prefix' => '<h2>' . $this->t('Other campaign metrics') . '</h2>',
      ];

      $stat_groups = [
        'bounces',
        'forwards',
        'opens',
        'clicks',
        'facebook_likes',
        'list_stats',
      ];

      foreach ($stat_groups as $group) {
        $content['metrics_table'][] = [
          'key' => [
            '#markup' => '<strong>' . ucfirst(str_replace('_', ' ', $group)) . '</strong>',
          ],
          'value' => [
            '#markup' => '',
          ],
        ];

        foreach ($response->{$group} as $key => $value) {
          if ($key == "last_open" && !empty($value)) {
            $value = $this->dateFormatter->format(strtotime($value), 'custom', 'F j, Y - g:ia');
          }

          $content['metrics_table'][] = [
            'key' => [
              '#markup' => $key,
            ],
            'value' => [
              '#markup' => $value,
            ],
          ];
        }
      }
    }
    else {
      $content['unavailable'] = [
        '#markup' => $this->t('The campaign stats are unavailable at this time.'),
      ];
    }

    return $content;
  }

  /**
   * Callback for entity title autocomplete field.
   *
   * @param string $entity_type
   *   The entity type to search by title.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing matched entity data.
   */
  public function entityAutocomplete($entity_type) {
    $q = $this->request->get('q');

    $query = $this->entityTypeManager->getStorage($entity_type)->getQuery()
      ->condition('title', $q, 'CONTAINS')
      ->range(0, 10);

    $entity_ids = $query->execute();

    $entities = [];

    if (!empty($entity_ids)) {
      $entities_data = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($entity_ids);

      if (!empty($entities_data)) {
        /* @var $entity \Drupal\Core\Entity\EntityInterface */
        foreach ($entities_data as $id => $entity) {
          $title = $entity->getTypedData()->getString('title');

          $entities[] = [
            'value' => $title . ' [' . $id . ']',
            'label' => Html::escape($title),
          ];
        }
      }
    }

    return new JsonResponse($entities);
  }

}
