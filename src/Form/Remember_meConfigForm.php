<?php

/**
 * @file
 * Contains \Drupal\remember_me\Form\Remember_meConfigForm.
 */

namespace Drupal\remember_me\Form;

use Drupal\Core\Datetime\Date;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


class Remember_meConfigForm extends ConfigFormBase {
  public function getFormId() {
    return 'remember_me_admin_settings';
  }
  public function getEditableConfigNames() {
    return [
      'remember_me.settings',
    ];

  }
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('remember_me.settings');
    $time_intervals = array(30, 60, 3600, 10800, 21600, 43200, 86400, 172800, 259200, 604800, 1209600, 2592000, 5184000, 7776000);
    $options = $this->build_options($time_intervals);

    $form['session'] = array(
      '#type' => 'item',
      '#markup' => t("yes"),
      '#title' => t('Session lifetime'),
      '#description' => t('Currently configured session cookie lifetime.'),
    );

    $form['remember_me_managed'] = array(
      '#type' => 'checkbox',
      '#title' => t('Manage session lifetime'),
      '#default_value' => $config->get('remember_me_managed', 1),
      '#description' => t('Choose to manually overwrite the configuration value from settings.php.'),
    );
    $form['remember_me_lifetime'] = array(
      '#type' => 'select',
      '#title' => t('Lifetime'),
      '#default_value' => $config->get('remember_me_lifetime', 604800),
      '#options' => $options,
      '#description' => t('Duration a user will be remembered for. This setting is ignored if Manage session lifetime (above) is disabled.'),
    );
    $form['remember_me_checkbox'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remember me field'),
      '#default_value' => $config->get('remember_me_checkbox', 1),
      '#description' => t('Default state of the "Remember me" field on the login forms.'),
    );
    $form['remember_me_checkbox_visible'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remember me field visible'),
      '#default_value' => $config->get('remember_me_checkbox_visible', 1),
      '#description' => t('Should the "Remember me" field be visible on the login forms.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements hook_form_submit().
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set values in variables.
    \Drupal::state()->set('remember_me_managed', $form_state->getValues()['remember_me_managed']);
    \Drupal::state()->set('remember_me_lifetime', $form_state->getValues()['remember_me_lifetime']);
    \Drupal::state()->set('remember_me_checkbox', $form_state->getValues()['remember_me_checkbox']);
    \Drupal::state()->set('remember_me_checkbox_visible', $form_state->getValues()['remember_me_checkbox_visible']);

    $config = \Drupal::service('config.factory')->getEditable('remember_me.settings');
    $config->set('remember_me_managed', $form_state->getValues()['remember_me_managed']);
    $config->set('remember_me_lifetime', $form_state->getValues()['remember_me_lifetime']);
    $config->set('remember_me_checkbox', $form_state->getValues()['remember_me_checkbox']);
    $config->set('remember_me_checkbox_visible', $form_state->getValues()['remember_me_checkbox_visible']);
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Helper function to display formatted time interval.
   * @param array $time_intervals
   * @param int $granularity
   * @param null $langcode
   * @return array
   */
  function build_options(array $time_intervals, $granularity = 2, $langcode = NULL) {
    $callback = function ($value) use ($granularity, $langcode) {
      return \Drupal::service('date.formatter')->formatInterval($value, $granularity, $langcode);
    };
    return array_combine($time_intervals, array_map($callback, $time_intervals));
  }
}

