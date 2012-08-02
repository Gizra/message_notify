<?php

/**
 * Test notifier.
 */
class MessageNotifierTest extends MessageNotifierBase {

  /**
   * Add Message notify view mode.
   */
  public static function viewModes() {
    return array(
      'foo' => array('label' => t('Foo')),
      'bar' => array('label' => t('Bar')),
    );
  }

  public function deliver(array $output = array()) {
    $this->message->output = $output;
    // Return TRUE or FALSE as it was set on the Message.
    return empty($this->fail);
  }

}
