<?php
/**
 * @file
 * Contains \Drupal\message_notify\Plugin\Notifier\MessageNotifierInterface.
 */

namespace Drupal\message_notify\Plugin\Notifier;

use Drupal\message\MessageInterface;

/**
 * Additional behaviors for a Entity Reference field.
 *
 * Implementations that wish to provide an implementation of this should
 * register it using CTools' plugin system.
 */
interface MessageNotifierInterface {

  /**
   * Constructor for the notifier.
   *
   * @param $plugin
   *   The notifier plugin object. Note the "options" values might have
   *   been overriden in message_notify_send_message().
   * @param \Drupal\message\MessageInterface $message
   *   The Message entity.
   */
  public function __construct(MessageNotifierInterface $plugin, MessageInterface $message);

  /**
   * Entry point to send and process a message.
   *
   * @return
   *   TRUE or FALSE based on delivery status.
   */
  public function send();

  /**
   * Deliver a message via the required transport method.
   *
   * @param array $output
   *   Array keyed by the view mode, and the rendered entity in the
   *   specified view mode.
   *
   * @return
   *   TRUE or FALSE based on delivery status.
   */
  public function deliver(array $output = array());

  /**
   * Post send operations.
   * @param $result
   * @param array $output
   * @return
   */
  public function postSend($result, array $output = array());

  /**
   * Determine if user can access notifier.
   */
  public function access();

}
