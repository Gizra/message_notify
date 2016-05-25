<?php
/**
 * @file
 * Contains \Drupal\message_notify\Tests\Notifier\TestNotifier.
 */

namespace Drupal\message_notify\Tests\Notifier;

use Drupal\message_notify\Plugin\Notifier\MessageNotifierBase;

/**
 * Test notifier.
 */
class TestNotifier extends MessageNotifierBase {

  /**
   * {@inheritdoc}
   */
  public function deliver(array $output = []) {
    $this->message->output = $output;
    // Return TRUE or FALSE as it was set on the Message.
    return empty($this->fail);
  }

}
