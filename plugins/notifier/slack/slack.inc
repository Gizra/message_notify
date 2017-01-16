<?php

if (module_exists('slack')) {
  $plugin = array(
    'title' => t('Slack'),
    'description' => t('Send Slack using the Slack module.'),
    'class' => 'MessageNotifierSlack',
    'view_modes' => array(
      'message_notify_slack_body' => array('label' => t('Notify - Slack Body')),
    ),
  );
}
