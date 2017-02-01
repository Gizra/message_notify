<?php

/**
 * @file
 * Message notify drush commands.
 */

/**
 * Implements hook_drush_command().
 */
function message_notify_drush_command() {
  $items = array();

  $items['message-resend'] = array(
    'callback' => 'message_notify_resend',
    'description' => 'Resend selected messages by minimal and maximal message IDs.',
    'arguments' => array(
      'min_mid' => 'Message ID to start from.',
      'max_mid' => 'Maximal message ID to resend.',
    ),
    'options' => array(
      'notifier' => "The notifier to use. 'email' by default.",
    ),
    'examples' => array(
      'drush message-resend 120 140' => 'Resend messages with ID between 120 and 140.',
    ),
  );

  return $items;
}

/**
 * Drush callback: Resend messages.
 */
function message_notify_resend($min_mid, $max_mid) {
  // Allow to change the notifier.
  $notifier = drush_get_option('notifier', 'email');

  // Fetch the messages.
  $query = new EntityFieldQuery();
  $result = $query->entityCondition('entity_type', 'message')
    ->propertyCondition('mid', array(intval($min_mid), intval($max_mid)), 'BETWEEN')
    ->execute();

  if (empty($result['message'])) {
    drush_log(dt('No messages found.'), 'ok');
    return;
  }

  $sent_count = 0;
  foreach (message_load_multiple(array_keys($result['message'])) as $message) {
    // Send the message.
    if (message_notify_send_message($message, array(), $notifier)) {
      drush_log(dt('Message #@mid resent.', array('@mid' => $message->mid)), 'success');
      $sent_count++;
    }
    else {
      drush_log(dt('Failed sending message #@mid.', array('@mid' => $message->mid)), 'error');
    }
  }

  drush_log(dt('Resent @count messages by @notifier.', array('@count' => $sent_count, '@notifier' => $notifier)), 'success');
}
