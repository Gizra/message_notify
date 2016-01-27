<?php

if (\Drupal::moduleHandler()->moduleExists('sms')) {
  $plugin = array(
    'title' => t('SMS'),
    'description' => t('Send SMS using the SMS framework module.'),
    'class' => 'MessageNotifierSMS',
    'view_modes' => array(
      'message_notify_sms_body' => array('label' => t('Notify - SMS Body')),
    ),
  );
}