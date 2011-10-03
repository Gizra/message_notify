<?php

/**
 * Plugin definition.
 */
$plugin = array(
  'name' => t('Daily digest'),
  'description' => t('Send the message as a daily digest.'),
);

/**
 * Process callback.
 */
function message_notify_default_process(Message $message) {
  // Check it's time to send the mail.
  return message_notify_send_email($message);
}