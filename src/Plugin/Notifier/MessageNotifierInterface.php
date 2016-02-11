<?php
/**
 * @file
 * Contains \Drupal\message_notify\Plugin\Notifier\MessageNotifierInterface.
 */

namespace Drupal\message_notify\Plugin\Notifier;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\message\MessageInterface;

/**
 * Additional behaviors for a Entity Reference field.
 *
 * Implementations that wish to provide an implementation of this should
 * register it using CTools' plugin system.
 */
interface MessageNotifierInterface extends ContainerFactoryPluginInterface {

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
   * Act upon send result.
   *
   * @param $result
   *   The result from delivery.
   * @param array $output
   *   The message output array.
   */
  public function postSend($result, array $output = array());

  /**
   * Determine if user can access notifier.
   */
  public function access();

  /**
   * Initialize the notifier.
   *
   * @todo can this be injected to the constructor?
   *
   * @param \Drupal\message\MessageInterface $message
   */
  public function init(MessageInterface $message);

}
