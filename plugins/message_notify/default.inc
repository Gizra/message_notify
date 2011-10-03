<?php

/**
 * Plugin definition.
 */
$plugin = array(
  'name' => t('Immediate'),
  'description' => t('Send the message as it is created. On failure queue the message for re-send.'),
  'presave callback' => 'message_notify_default_presave',
  'process callback' => 'message_notify_send_email',
);

/**
 * Presave callback.
 */
function message_notify_default_presave(Message $message) {
  if (message_notify_send_email($message)) {
    // If email was sent and result is TRUE we don't need to save message.
    $message->skip_save = TRUE;
  }
}

