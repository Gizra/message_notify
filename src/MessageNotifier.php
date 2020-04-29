<?php

namespace Drupal\message_notify;

use Drupal\message\MessageInterface;
use Drupal\message_notify\Exception\MessageNotifyException;
use Drupal\message_notify\Plugin\Notifier\Manager;

/**
 * Prepare and send notifications.
 */
class MessageNotifier implements MessageNotifierInterface {

  /**
   * The notifier plugin manager.
   *
   * @var \Drupal\message_notify\Plugin\Notifier\Manager
   */
  protected $notifierManager;

  /**
   * Constructs the message notifier.
   *
   * @param \Drupal\message_notify\Plugin\Notifier\Manager $notifier_manager
   *   The notifier plugin manager.
   */
  public function __construct(Manager $notifier_manager) {
    $this->notifierManager = $notifier_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function send(MessageInterface $message, array $options = [], $notifier_name = 'email') {
    if (!$this->notifierManager->hasDefinition($notifier_name, FALSE)) {
      throw new MessageNotifyException('Could not send notification using the "' . $notifier_name . '" notifier.');
    }

    /** @var \Drupal\message_notify\Plugin\Notifier\MessageNotifierInterface $notifier */
    $notifier = $this->notifierManager->createInstance($notifier_name, $options, $message);

    if ($notifier->access()) {
      return $notifier->send();
    }
    return FALSE;
  }

}
