<?php

namespace Drupal\mailchimp_signup\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\mailchimp_signup\Entity\MailchimpSignup;

/**
 * Subscribe to a Mailchimp list/audience.
 */
class MailchimpSignupPageForm extends FormBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * MailchimpSignupPageForm constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * The ID for this form.
   *
   * Set as class property so it can be overwritten as needed.
   *
   * @var string
   */
  private $formId = 'mailchimp_signup_page_form';

  /**
   * The MailchimpSignup entity used to build this form.
   *
   * @var \Drupal\mailchimp_signup\Entity\MailchimpSignup
   */
  private $signup = NULL;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->formId;
  }

  /**
   * Sets the form ID.
   *
   * @param string $formId
   *   The form ID.
   */
  public function setFormId($formId) {
    $this->formId = $formId;
  }

  /**
   * Sets the signup service.
   *
   * @param \Drupal\mailchimp_signup\Entity\MailchimpSignup $signup
   *   THe signup service.
   */
  public function setSignup(MailchimpSignup $signup) {
    $this->signup = $signup;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailchimp_signup.page_form'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];
    $settings = $this->signup->settings;

    $form['#attributes'] = ['class' => ['mailchimp-signup-subscribe-form']];

    $form['description'] = [
      '#markup' => $this->signup->description,
    ];

    // If the form is configured to submit via AJAX, add an empty container
    // element. This element is going to be replaced with the returned AJAX
    // response.
    if (isset($this->signup->settings['ajax_submit']) && $this->signup->settings['ajax_submit']) {
      $form['response'] = [
        '#prefix' => '<div id="mailchimp-response-' . $this->formId . '-wrapper">',
        '#suffix' => '</div>',
      ];
    }

    $form['mailchimp_lists'] = ['#tree' => TRUE];

    $lists = mailchimp_get_lists($this->signup->mc_lists);

    $lists_count = (!empty($lists)) ? count($lists) : 0;

    if (empty($lists)) {
      return [
        'message' => [
          '#markup' => $this->t('The subscription service is currently unavailable. Please check again later.'),
        ],
      ];
    }

    $list = [];
    if ($lists_count > 1) {
      // Default behavior.
      // The only difference here is that we've moved the default
      // value here, as a variable.
      $show_lists_and_groups = TRUE;

      // If we have selected the new option to pre-configure the
      // interest groups, that means that we need to hide the
      // container alongside the rendered checkboxes from the user.
      if (isset($this->signup->settings['configure_groups']) && $this->signup->settings['configure_groups']) {
        $show_lists_and_groups = FALSE;
      }

      foreach ($lists as $list) {
        // Wrap in a div:
        $wrapper_key = 'mailchimp_' . $list->id;
        $subscribe_to_list = FALSE;

        // If we have selected the pre-configure option, we need to populate
        // the data the same way, before introducing the new functionality.
        if (isset($settings['configure_groups']) && isset($settings['group_items'])) {
          if (array_key_exists($list->id, $settings['group_items'])) {
            $subscribe_to_list = $list->id;
          }
        }

        $form['mailchimp_lists'][$wrapper_key] = [
          '#prefix' => '<div id="mailchimp-newsletter-' . $list->id . '" class="mailchimp-newsletter-wrapper">',
          '#suffix' => '</div>',
        ];

        $form['mailchimp_lists'][$wrapper_key]['subscribe'] = [
          '#type' => 'checkbox',
          '#title' => $list->name,
          '#return_value' => $list->id,
          '#default_value' => $subscribe_to_list,
          '#access' => $show_lists_and_groups,
        ];

        if ($this->signup->settings['include_interest_groups'] && isset($list->intgroups)) {
          $form['mailchimp_lists'][$wrapper_key]['interest_groups'] = [
            '#type' => 'fieldset',
            '#access' => $show_lists_and_groups,
            '#title' => $this->t('Interest Groups for %label', ['%label' => $list->name]),
            '#states' => [
              'invisible' => [
                ':input[name="mailchimp_lists[' . $wrapper_key . '][subscribe]"]' => ['checked' => FALSE],
              ],
            ],
          ];

          // Create the form elements for all interest groups
          // and select the ones needed.
          $defaults = [];
          $groups_items = isset($this->signup->settings['group_items']) ? $this->signup->settings['group_items'] : [];

          if (isset($groups_items[$list->id])) {
            $defaults = $groups_items[$list->id];
          }

          $form['mailchimp_lists'][$wrapper_key]['interest_groups'] += mailchimp_interest_groups_form_elements($list, $defaults);
        }
      }
    }
    else {
      $list = reset($lists);

      // Default behavior.
      // The only difference here is that we've moved the default
      // value here, as a variable.
      $show_lists_and_groups = TRUE;

      // If we have selected the new option to pre-configure the
      // interest groups, that means that we need to hide the
      // container alongside the rendered checkboxes from the user.
      if (isset($this->signup->settings['configure_groups']) && $this->signup->settings['configure_groups']) {
        $show_lists_and_groups = FALSE;
      }

      // Create the form elements for all interest groups
      // and select the ones needed.
      $defaults = [];
      $groups_items = isset($this->signup->settings['group_items']) ? $this->signup->settings['group_items'] : [];

      if (isset($groups_items[$list->id])) {
        $defaults = $groups_items[$list->id];
      }

      if ($this->signup->settings['include_interest_groups'] && isset($list->intgroups)) {
        $form['mailchimp_lists']['#weight'] = 9;
        $form['mailchimp_lists']['interest_groups'] = mailchimp_interest_groups_form_elements($list, $defaults);
        $form['mailchimp_lists']['#access'] = $show_lists_and_groups;
      }
    }

    $mergevars_wrapper_id = isset($list->id) ? $list->id : '';
    $form['mergevars'] = [
      '#prefix' => '<div id="mailchimp-newsletter-' . $mergevars_wrapper_id . '-mergefields" class="mailchimp-newsletter-mergefields">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    foreach ($this->signup->settings['mergefields'] as $tag => $mergevar_str) {
      if (!empty($mergevar_str)) {
        $mergevar = unserialize($mergevar_str);
        $form['mergevars'][$tag] = mailchimp_insert_drupal_form_tag($mergevar);
        if (empty($lists)) {
          $form['mergevars'][$tag]['#disabled'] = TRUE;
        }
      }
    }
    // Include the GDPR consent checkbox if necessary
    if (!empty($this->signup->settings['gdpr_consent'])) {
      $form['gdpr_consent'] = [
        '#type' => 'checkbox',
        '#default_value' => FALSE,
        '#title' => $this->signup->settings['gdpr_checkbox_label'],
        '#required' => isset($this->signup->settings['gdpr_consent_required']) ? $this->signup->settings['gdpr_consent_required'] : FALSE,
      ];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t($this->signup->settings['submit_button']),
      '#disabled' => (empty($lists)),
    ];

    // Add a wrapper element to the form if it is configured for AJAX submits.
    if (isset($this->signup->settings['ajax_submit']) && $this->signup->settings['ajax_submit']) {
      $form_wrapper = Html::getId($this->formId);
      $response_wrapper = "mailchimp-response-{$this->formId}-wrapper";
      $form['actions']['submit']['#id'] = $form_wrapper . '-edit-submit';
      $form['#prefix'] = '<div id="' . $form_wrapper . '">';
      $form['#suffix'] = '</div>';
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::ajaxSubmit',
        'response_wrapper' => $response_wrapper,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $signup = $build_info['callback_object']->signup;

    // For forms that allow subscribing to multiple lists/audiences
    // ensure at least one list/audience has been selected.
    // Get the enabled lists/audiences for this form.
    $enabled_lists = array_filter($signup->mc_lists);
    if (count($enabled_lists) > 1) {

      // Filter the selected lists out of the form values.
      $selected_lists = array_filter($form_state->getValue('mailchimp_lists'),
        function ($list) {
          return $list['subscribe'];
        }
      );

      // If a list has been selected, validation passes.
      if (!empty($selected_lists)) {
        return;
      }

      $form_state->setErrorByName('mailchimp_lists', $this->t("Please select at least one audience to subscribe to."));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    global $base_url;

    $list_details = mailchimp_get_lists($this->signup->mc_lists);

    $subscribe_lists = [];

    // Filter out blank fields so we don't erase values on the Mailchimp side.
    $mergevars = array_filter($form_state->getValue('mergevars'));

    $email = $mergevars['EMAIL'];

    $gdpr_consent = $form_state->getValue('gdpr_consent');

    $mailchimp_lists = $form_state->getValue('mailchimp_lists');

    // If we only have one list we won't have checkbox values to investigate.
    if (count(array_filter($this->signup->mc_lists)) == 1) {
      $subscribe_lists[0] = [
        'subscribe' => reset($this->signup->mc_lists),
        'interest_groups' => isset($mailchimp_lists['interest_groups']) ? $mailchimp_lists['interest_groups'] : NULL,
      ];
    }
    else {
      // We can look at the checkbox values now.
      foreach ($mailchimp_lists as $list) {
        if ($list['subscribe']) {
          $subscribe_lists[] = $list;
        }
      }
    }

    $successes = [];

    // Loop through the selected lists and try to subscribe.
    foreach ($subscribe_lists as $list_choices) {
      $list_id = $list_choices['subscribe'];

      $interests = isset($list_choices['interest_groups']) ? $list_choices['interest_groups'] : [];
      if (isset($this->signup->settings['safe_interest_groups']) && $this->signup->settings['safe_interest_groups']) {
        $current_status = mailchimp_get_memberinfo($list_id, $email);
        if (isset($current_status->interests)) {
          $current_interests = [];
          foreach ($current_status->interests as $id => $selected) {
            if ($selected) {
              $current_interests[$id] = $id;
            }
          }
          $interests[] = $current_interests;
        }
      }
      $result = mailchimp_subscribe($list_id, $email, $mergevars, $interests, $this->signup->settings['doublein'], 'html', NULL, $gdpr_consent);

      if (empty($result)) {
        $this->messenger->addWarning($this->t('There was a problem with your newsletter signup to %list.', [
          '%list' => $list_details[$list_id]->name,
        ]));
      }
      else {
        $successes[] = $list_details[$list_id]->name;
      }
    }

    if (count($successes) && strlen($this->signup->settings['confirmation_message'])) {
      $this->messenger->addStatus($this->t($this->signup->settings['confirmation_message']));
      $form_state->set('mailchimp_success', TRUE);
    }

    $destination = $this->signup->settings['destination'];
    if (empty($destination)) {
      $destination_url = Url::fromRoute('<current>');
    }
    else {
      $destination_url = Url::fromUri($base_url . '/' . $this->signup->settings['destination']);
    }

    $form_state->setRedirectUrl($destination_url);
  }

  /**
   * Ajax submit handler.
   *
   * @param array $form
   *   The form itself.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   A ajax response.
   */
  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response_wrapper_id = '#' . $form['actions']['submit']['#ajax']['response_wrapper'];

    // Simply return any status messages as a content the the response wrapper.
    $status_messages = ['#type' => 'status_messages'];
    $content = \Drupal::service('renderer')->renderRoot($status_messages);
    $response->addCommand(new HtmlCommand($response_wrapper_id, $content));

    // Also add an extra class to the form itself to signify the form changed.
    $form_wrapper_id = '#' . Html::getId($this->formId);
    $response->addCommand(new ChangedCommand($form_wrapper_id));

    // Reset form values if submission was successful.
    if ($form_state->get('mailchimp_success')) {
      $response->addCommand(new InvokeCommand("$form_wrapper_id form,$form_wrapper_id", 'trigger', ['reset']));
    }

    return $response;
  }

}
