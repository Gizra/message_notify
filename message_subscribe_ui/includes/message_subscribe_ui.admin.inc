<?php

/**
 * @file
 * Administration settings for Message subscribe UI.
 */

/**
 * Menu callback; Admin settings form.
 */
function message_subscribe_ui_admin_settings() {
  $form['message_subscribe_default_notifiers'] = array(
    '#type' => 'select',
    '#title' => t('Default message notifiers'),
    '#description' => t('Which message notifiers will be added to every subscription.'),
    '#default_value' => variable_get('message_subscribe_default_notifiers', 'email'),
    '#multiple' => TRUE,
    '#options' => message_notify_get_notifiers_as_options(),
    '#required' => FALSE,
  );

  // For every subscription flag, show a view selection.
  foreach (message_subscribe_flag_get_flags() as $flag) {
    $name = 'message_' . $flag->name;
    $params = array('@title' => $flag->title);

    $form[$name] = array(
      '#type' => 'select',
      '#title' => t('View for flag <em>@title</em>', $params),
      '#description' => t('Select the View that should be used for flag @title.', $params),
      '#options' => views_get_views_as_options(),
      '#default_value' => variable_get($name, 'subscribe_' . $flag->content_type . ':default'),
      '#required' => TRUE,
    );
  }

  return system_settings_form($form);
}
