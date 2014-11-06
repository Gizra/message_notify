<?php

/**
 * Interface for logging notification sending of messages.
 */
interface MessageNotifierLoggerInterface {

  public function __construct($plugin, MessageNotifierInterface $notifier);


  /**
   * Log the sending of a message.
   *
   * @param $result
   *   The sending result from the notifier class.
   * @param $message
   *   The message that was sent.
   */
  public function log($result, $message);

}