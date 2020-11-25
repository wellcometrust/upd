<?php

namespace Drupal\mailchimp_signup\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the MailchimpSignup entity edit form.
 *
 * @ingroup mailchimp_signup
 */
class MailchimpSignupForm extends EntityForm {

  /**
   * The router builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * The entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(RouteBuilderInterface $router_builder, QueryFactory $entity_query) {
    $this->routerBuilder = $router_builder;
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.builder'),
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $signup = $this->entity;

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#size' => 35,
      '#maxlength' => 32,
      '#default_value' => $signup->title,
      '#description' => $this->t('The title for this signup form.'),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $signup->id,
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => array(
        'source' => array('title'),
        'exists' => 'mailchimp_signup_load',
      ),
      '#description' => $this->t('A unique machine-readable name for this audience. It must only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$signup->isNew(),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => 'Description',
      '#default_value' => isset($signup->settings['description']) ? $signup->settings['description'] : '',
      '#rows' => 2,
      '#maxlength' => 500,
      '#description' => $this->t('This description will be shown on the signup form below the title. (500 characters or less)'),
    );
    $mode_defaults = array(
      MAILCHIMP_SIGNUP_BLOCK => array(MAILCHIMP_SIGNUP_BLOCK),
      MAILCHIMP_SIGNUP_PAGE => array(MAILCHIMP_SIGNUP_PAGE),
      MAILCHIMP_SIGNUP_BOTH => array(MAILCHIMP_SIGNUP_BLOCK, MAILCHIMP_SIGNUP_PAGE),
    );
    $form['mode'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Display Mode',
      '#required' => TRUE,
      '#options' => array(
        MAILCHIMP_SIGNUP_BLOCK => 'Block',
        MAILCHIMP_SIGNUP_PAGE => 'Page',
      ),
      '#default_value' => !empty($signup->mode) ? $mode_defaults[$signup->mode] : array(),
    );

    $form['settings'] = array(
      '#type' => 'details',
      '#title' => 'Settings',
      '#tree' => TRUE,
      '#open' => TRUE,
    );

    $form['settings']['path'] = array(
      '#type' => 'textfield',
      '#title' => 'Page URL',
      '#description' => $this->t('Path to the signup page. ie "newsletter/signup".'),
      '#default_value' => isset($signup->settings['path']) ? $signup->settings['path'] : NULL,
      '#states' => array(
        // Hide unless needed.
        'visible' => array(
          ':input[name="mode[' . MAILCHIMP_SIGNUP_PAGE . ']"]' => array('checked' => TRUE),
        ),
        'required' => array(
          ':input[name="mode[' . MAILCHIMP_SIGNUP_PAGE . ']"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['settings']['submit_button'] = array(
      '#type' => 'textfield',
      '#title' => 'Submit Button Label',
      '#required' => 'TRUE',
      '#default_value' => isset($signup->settings['submit_button']) ? $signup->settings['submit_button'] : 'Submit',
    );

    $form['settings']['confirmation_message'] = array(
      '#type' => 'textfield',
      '#title' => 'Confirmation Message',
      '#description' => 'This message will appear after a successful submission of this form. Leave blank for no message, but make sure you configure a destination in that case unless you really want to confuse your site visitors.',
      '#default_value' => isset($signup->settings['confirmation_message']) ? $signup->settings['confirmation_message'] : 'You have been successfully subscribed.',
    );

    $form['settings']['destination'] = array(
      '#type' => 'textfield',
      '#title' => 'Form destination page',
      '#description' => 'Leave blank to stay on the form page.',
      '#default_value' => isset($signup->settings['destination']) ? $signup->settings['destination'] : NULL,
    );

    $form['mc_lists_config'] = array(
      '#type' => 'details',
      '#title' => $this->t('Mailchimp Audience Selection & Configuration'),
      '#open' => TRUE,
    );
    $lists = mailchimp_get_lists();
    $options = array();
    foreach ($lists as $mc_list) {
      $options[$mc_list->id] = $mc_list->name;
    }
    $mc_admin_url = Link::fromTextAndUrl('Mailchimp', Url::fromUri('https://admin.mailchimp.com', array('attributes' => array('target' => '_blank'))));
    $form['mc_lists_config']['mc_lists'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Mailchimp Audiences'),
      '#description' => $this->t('Select which audiences to show on your signup form. You can create additional audiences at @Mailchimp.',
        array('@Mailchimp' => $mc_admin_url->toString())),
      '#options' => $options,
      '#default_value' => is_array($signup->mc_lists) ? $signup->mc_lists : array(),
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => '::mergefields_callback',
        'wrapper' => 'mergefields-wrapper',
        'method' => 'replace',
        'effect' => 'fade',
        'progress' => array(
          'type' => 'throbber',
          'message' => $this->t('Retrieving merge fields for this audience.'),
        ),
      ),
    );

    $form['mc_lists_config']['mergefields'] = array(
      '#prefix' => '<div id="mergefields-wrapper">',
      '#suffix' => '</div>',
    );

    // Show merge fields if changing list/audience field or editing existing list/audience.
    if ($form_state->getValue('mc_lists') || !$signup->isNew()) {
      $form['mc_lists_config']['mergefields'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Merge Field Display'),
        '#description' => $this->t('Select the merge fields to show on registration forms. Required fields are automatically displayed.'),
        '#id' => 'mergefields-wrapper',
        '#tree' => TRUE,
        '#weight' => 20,
      );

      $mc_lists = $form_state->getValue('mc_lists') ? $form_state->getValue('mc_lists') : $signup->mc_lists;

      $mergevar_options = $this->getMergevarOptions($mc_lists);

      foreach ($mergevar_options as $mergevar) {
        $form['mc_lists_config']['mergefields'][$mergevar->tag] = array(
          '#type' => 'checkbox',
          '#title' => Html::escape($mergevar->name),
          '#default_value' => isset($signup->settings['mergefields'][$mergevar->tag]) ? !empty($signup->settings['mergefields'][$mergevar->tag]) : TRUE,
          '#required' => $mergevar->required,
          '#disabled' => $mergevar->required,
        );
      }
    }

    $form['subscription_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('Subscription Settings'),
      '#open' => TRUE,
    );

    $form['subscription_settings']['doublein'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Require subscribers to Double Opt-in'),
      '#description' => $this->t('New subscribers will be sent a link with an email they must follow to confirm their subscription.'),
      '#default_value' => isset($signup->settings['doublein']) ? $signup->settings['doublein'] : FALSE,
    );

    $form['subscription_settings']['include_interest_groups'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Include interest groups on subscription form.'),
      '#default_value' => isset($signup->settings['include_interest_groups']) ? $signup->settings['include_interest_groups'] : FALSE,
      '#description' => $this->t('If set, subscribers will be able to select applicable interest groups on the signup form.'),
    );

    $form['subscription_settings']['safe_interest_groups'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t("Don't opt-out of interest groups: only opt-in."),
      '#default_value' => isset($signup->settings['safe_interest_groups']) ? $signup->settings['safe_interest_groups'] : FALSE,
      '#description' => $this->t('This is useful for "additive" form behavior, so a user adding a new interest will not have other interests removed from their Mailchimp subscription just because they failed to check the box again.'),
      '#states' => array(
        // Hide unless needed.
        'visible' => array(
          ':input[name="include_interest_groups"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['subscription_settings']['configure_groups'] = [
      '#type' => 'checkbox',
      '#title' => t('Pre-configure interest groups'),
      '#default_value' => isset($signup->settings['configure_groups']) ? $signup->settings['configure_groups'] : FALSE,
      '#description' => t('If set, the end-user will not be able to select groups, but rather subscribe to the ones selected by default'),
      '#states' => [
        'visible' => [
          ':input[name="include_interest_groups"]' => ['checked' => TRUE],
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'configureInterestGroups'],
        'wrapper' => 'interest-groups-container',
      ],
    ];

    $form['subscription_settings']['groups_container'] = [
      '#type' => 'container',
      '#title' => t('Configure interest groups'),
      '#prefix' => '<div id="interest-groups-container">',
      '#suffix' => '</div>',
      '#open' => TRUE,
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="configure_groups"]' => ['checked' => TRUE],
        ],
      ],
    ];
    
    $form['subscription_settings']['gdpr_consent'] = array(
      '#type' => 'checkbox',
      '#title' => t('Add a GDPR consent checkbox'),
      '#description' => t('Add a GDPR consent checkbox to the signup form that syncs with the Mailchimp marketing permission field.'),
      '#default_value' => isset($signup->settings['gdpr_consent']) ? $signup->settings['gdpr_consent'] : FALSE,
    );
 
    $form['subscription_settings']['gdpr_checkbox_label'] = array(
      '#type' => 'textfield',
      '#title' => t('Consent checkbox label'),
      '#description' => t('The label to display on the GDPR consent checkbox, for example "I agree to the privacy policy". This should coincide with what you have in Mailchimp!'),
      '#default_value' => isset($signup->settings['gdpr_checkbox_label']) ? $signup->settings['gdpr_checkbox_label'] : NULL,
      '#states' => array(
        // Hide unless needed.
        'visible' => array(
          ':input[name="gdpr_consent"]' => array('checked' => TRUE),
        ),
        'required' => array(
          ':input[name="gdpr_consent"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['subscription_settings']['gdpr_consent_required'] = array(
      '#type' => 'checkbox',
      '#title' => t('GDPR consent required'),
      '#description' => t('Make the GDPR consent checkbox a required field.'),
      '#default_value' => isset($signup->settings['gdpr_consent_required']) ? $signup->settings['gdpr_consent_required'] : FALSE,
      '#states' => array(
        // Hide unless needed.
        'visible' => array(
          ':input[name="gdpr_consent"]' => array('checked' => TRUE),
        ),
      ),
    );

    // An empty array as a default value for all of the interest groups.
    $groups = [];
    $configure_groups = FALSE;

    if (isset($signup->settings['configure_groups']) && $signup->settings['configure_groups']) {
      $configure_groups = TRUE;
    }

    if ($form_state->getValue('configure_groups') || $configure_groups) {
      // Grab a reference to the selected list - either when an AJAX
      // callback is executed or when we are in "Edit" mode.
      if (!$selected_lists = $form_state->getValue('mc_lists')) {
        $selected_lists = $signup->mc_lists;
      }

      if ($selected_lists && is_array($selected_lists)) {
        // We don't want to query the API for all lists
        // besides the one selected, so we filter them out.
        $selected_lists = array_filter($selected_lists);

        // Default value for the list-specific groups.
        if (!$groups_items = $signup->settings['group_items']) {
          $groups_items = [];
        }

        // Grab a reference to each list using the module built-in function.
        foreach (mailchimp_get_lists($selected_lists) as $list) {
          $default = [];

          // If we have some items already selected - add them to the
          // default array so that they are selected in case we
          // trigger another AJAX callback.
          if (array_key_exists($list->id, $groups_items)) {
            $default = $groups_items[$list->id];
          }

          // Merge the lists for each of the selected groups here and return
          // a renderable array for the form to display.
          // We need to do this here, in case there is more than 1 list selected
          // and we need to return the interest groups for all of them (if present).
          $groups = array_merge(
            $groups,
            [$list->id => mailchimp_interest_groups_form_elements($list, $default, NULL, 'admin')]
          );
        }
      }
    }

    $form['subscription_settings']['groups_container']['items'] = $groups;

    return $form;
  }

  /**
   * AJAX callback handler for the interest groups.
   */
  public function configureInterestGroups(array &$form, FormStateInterface $form_state) {
    return $form['subscription_settings']['groups_container'];
  }

  /**
   * AJAX callback handler for MailchimpSignupForm.
   */
  public function mergefields_callback(&$form, FormStateInterface $form_state) {
    // We can return either a form element here (default functionality) or
    // an instance of the AjaxResponse with the appropriate commands
    // to be executed.
    $response = new AjaxResponse();

    // This one acts as a replacement for the original return value that
    // was defined here beforehand. Simply replace the merge fields
    // for each of the selected mailing lists.
    $response->addCommand(new HtmlCommand(
      '#mergefields-wrapper',
      $form['mc_lists_config']['mergefields']
    ));

    // This one here is coupled to the functionality for the interest groups.
    // It's required because of the following scenarion:
    // The Ajax callback mapped to the "configure_groups" checkbox is triggered
    // once the checkbox is selected, thus loading the interest groups for all
    // of the selected mailing lists. But in case a user tries to selected/unselect
    // a mailing list afterwards, the callback will no longer execute. So if we
    // add a new command here for that specific container, we can replacee the list.
    if ($form_state->getValue('configure_groups')) {
      $response->addCommand(new HtmlCommand(
        '#interest-groups-container',
        $form['subscription_settings']['groups_container']
      ));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $mode = $form_state->getValue('mode');

    /* @var $signup \Drupal\mailchimp_signup\Entity\MailchimpSignup */
    $signup = $this->getEntity();
    $signup->mode = array_sum($mode);

    $mergefields = $form_state->getValue('mergefields', []);

    $mc_lists = $form_state->getValue('mc_lists') ? $form_state->getValue('mc_lists') : $signup->mc_lists;

    $mergevar_options = $this->getMergevarOptions($mc_lists);

    foreach ($mergefields as $id => $val) {
      if ($val) {
        // Can't store objects in configuration; serialize this.
        $mergefields[$id] = serialize($mergevar_options[$id]);
      }
    }

    // This can occur when the form is submitted without JS enabled.
    if (!isset($signup->mergefields)) {
      $signup->mergefields['EMAIL'] = TRUE;
    }
    if (empty($mergefields)) {
      $mergefields['EMAIL'] = serialize($mergevar_options['EMAIL']);
    }

    $signup->settings['mergefields'] = $mergefields;
    $signup->settings['description'] = $form_state->getValue('description');
    $signup->settings['doublein'] = $form_state->getValue('doublein');
    $signup->settings['include_interest_groups'] = $form_state->getValue('include_interest_groups');
    $signup->settings['safe_interest_groups'] = $form_state->getValue('safe_interest_groups');
    $signup->settings['configure_groups'] = $form_state->getValue('configure_groups');
    $signup->settings['gdpr_consent'] = $form_state->getValue('gdpr_consent');
    $signup->settings['gdpr_checkbox_label'] = $form_state->getValue('gdpr_checkbox_label');
    $signup->settings['gdpr_consent_required'] = $form_state->getValue('gdpr_consent_required');

    $groups_items = [];
    $groups_settings = $form_state->getValue('groups_container');

    if (isset($groups_settings['items'])) {
      $signup->settings['group_items'] = $groups_settings['items'];
    }

    // Clear path value if mode doesn't include signup page.
    if (!isset($mode[MAILCHIMP_SIGNUP_PAGE])) {
      $signup->settings['path'] = '';
    }

    $signup->save();

    $this->routerBuilder->setRebuildNeeded();

    $form_state->setRedirect('mailchimp_signup.admin');
  }



  public function exist($id) {
    $entity = $this->entityQuery->get('mailchimp_signup')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  private function getMergevarOptions(array $mc_lists) {
    $mergevar_settings = mailchimp_get_mergevars(array_filter($mc_lists));
    $mergevar_options = array();
    foreach ($mergevar_settings as $list_mergevars) {
      foreach ($list_mergevars as $mergevar) {
        if (isset($mergevar->public) && $mergevar->public) {
          $mergevar_options[$mergevar->tag] = $mergevar;
        }
      }
    }

    return $mergevar_options;
  }

}
