<?php

/**
 * Simple class the implements logging to the watchdog.
 */
class MessageNotifierLoggerWatchdog implements MessageNotifierLoggerInterface {

  public function __construct($plugin, MessageNotifierInterface $notifier) {
    $this->plugin = $plugin;
    $this->notifier = $notifier;
  }

  public function log($result, $message) {
    $options = $this->plugin['options'];
    $notifier_definition = $this->notifier->getPluginDefinition();
    if (!$result && $options['log on fail']) {
      watchdog('message_notify', t('Could not send message using @title to user ID @uid.'), array('@title' => $notifier_definition['title'], '@uid' => $message->uid), WATCHDOG_ERROR);
    }
    elseif ($result && $options['log on success']) {
      watchdog('message_notify', t('Sent message using @title to user ID @uid.'), array('@title' => $notifier_definition['title'], '@uid' => $message->uid), WATCHDOG_INFO);
    }
  }
}
