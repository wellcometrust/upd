<?php

namespace Drupal\mailchimp_signup\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides block plugin definitions for Mailchimp Signup blocks.
 *
 * @see \Drupal\mailchimp_signup\Plugin\Block\MailchimpSignupSubscribeBlock
 */
class MailchimpSignupSubscribeBlock extends DeriverBase {

  use StringTranslationTrait;
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $signups = mailchimp_signup_load_multiple();

    /* @var $signup \Drupal\mailchimp_signup\Entity\MailchimpSignup */
    foreach ($signups as $signup) {
      if (intval($signup->mode) == MAILCHIMP_SIGNUP_BLOCK || intval($signup->mode) == MAILCHIMP_SIGNUP_BOTH) {

        $this->derivatives[$signup->id] = $base_plugin_definition;
        $this->derivatives[$signup->id]['admin_label'] = $this->t('Mailchimp Subscription Form: @name', ['@name' => $signup->label()]);
      }
    }

    return $this->derivatives;
  }

}
