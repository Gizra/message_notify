<?php

namespace Drupal\message_notify;

use Drupal\message\MessageInterface;

/**
 * Message notifier manager interface.
 */
interface MessageNotifierInterface {

  /**
   * Process and send a message.
   *
   * @param \Drupal\message\MessageInterface $message
   *   The message entity being used for the notification.
   * @param array $options
   *   Array of options to override the plugin's default ones.
   * @param string $notifier_name
   *   Optional; The name of the notifier to use. Defaults to "email"
   *   sending method.
   *
   * @return bool
   *   Boolean value denoting success or failure of the notification.
   *
   * @throws \Drupal\message_notify\Exception\MessageNotifyException
   *   If no matching notifier plugin exists.
   */
  public function send(MessageInterface $message, array $options = [], $notifier_name = 'email');

}
