<?php

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
   * @param Message $message
   *   The Message entity.
   */
  public function __construct($plugin, Message $message);

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
   * @param $output
   *   Array keyed by the view mode, and the rendered entity in the
   *   specified view mode.
   *
   * @return
   *   TRUE or FALSE based on delivery status.
   */
  public function deliver(array $output = array());

  /**
   * Post send operations.
   */
  public function postSend($result, array $output = array());

  /**
   * Determine if user can access notifier.
   */
  public function access();
}

/**
 * An abstract implementation of MessageNotifierInterface.
 */
abstract class MessageNotifierBase implements MessageNotifierInterface {

  /**
   * @var array The plugin definition.
   */
  protected $plugin;

  /**
   * @var Message The message entity.
   */
  protected $message;

  public function __construct($plugin, Message $message) {
    $this->plugin = $plugin;
    $this->message = $message;
  }

  /**
   * Renders the view modes needed for the plug-in.
   *
   * @return array
   *   view_mode => rendered output for it.
   */
  protected function render() {
    $output = array();
    foreach (array_keys($this->plugin['view_modes']) as $view_mode) {
      $content = $this->message->buildContent($view_mode);
      $output[$view_mode] = render($content);
    }
    return $output;
  }

  /**
   * Sending the message as a notification on the givven channel.
   *
   * @return bool
   *   Delivery status result. TRUE on success.
   */
  public function send() {
    $output = $this->render();
    $result = $this->deliver($output);
    $this->postSend($result, $output);
    return $result;
  }

  public function deliver(array $output = array()) {}

  /**
   * Act upon send result.
   *
   * - Save the rendered messages if needed.
   * - Invoke watchdog error on failure.
   */
  public function postSend($result, array $output = array()) {
    if (($save = $this->shouldSave($result))) {
      $this->handleRenderedFields($output);
      $this->message->save();
    }
    return $save;
  }

  /**
   * Handles the assignment of rendered output to fields on the message.
   *
   * Configuration is taken from plugin['options']['rendered fields'] list.
   *
   * @param array $output
   *   Mapping of the view_mode -> rendered result, collected after delivery.
   */
  protected function handleRenderedFields($output) {
    if (empty($this->plugin['options']['rendered fields'])) {
      // Bail early, when nothing is configured.
      return;
    }

    // Save the rendered output into matching fields.
    $wrapper = entity_metadata_wrapper('message', $this->message);
    foreach ($this->plugin['view_modes'] as $view_mode => $mode) {
      if (empty($this->plugin['options']['rendered fields'][$view_mode])) {
        $this->handleError('The rendered view mode @mode cannot be saved to field, as there is not a matching one.', array(
          '@mode' => $mode['label'],
        ));
        continue;
      }
      $field_name = $this->plugin['options']['rendered fields'][$view_mode];

      if (!$field = field_info_field($field_name)) {
        $this->handleError('Field @field does not exist.', array(
          '@field' => $field_name,
        ));
        continue;
      }

      // Get the format from the field. We assume the first delta is the
      // same as the rest.
      if (empty($wrapper->{$field_name}->format)) {
        $wrapper->{$field_name}->set($output[$view_mode]);
      }
      else {
        $format = $wrapper->type->{MESSAGE_FIELD_MESSAGE_TEXT}->get(0)->format->value();
        $wrapper->{$field_name}->set(array('value' => $output[$view_mode], 'format' => $format));
      }
    }
  }

  /**
   * Utility to throw exceptions.
   *
   * Exposed like this, to allow silencnig them from ancestors.
   *
   * @param string $error_text
   *   Error message.
   * @param array $variables
   *   List of variables to interpolate in $error_text.
   *
   * @throws MessageNotifyException
   *   Always
   */
  protected function handleError($error_text, array $variables = array()) {
    throw new MessageNotifyException(format_string($error_text, $variables));
  }

  /**
   * Utility to decide, whether to save the incomming message or not.
   *
   * @param bool $result
   *   Delivery function's result value.
   *
   * @return bool
   *   When TRUE, the messages should be saved to DB.
   */
  protected function shouldSave($result) {
    $save = FALSE;
    if (!$result) {
      watchdog('message_notify', 'Could not send message using @title to user ID @uid.', array(
        '@title' => $this->plugin['title'],
        '@uid' => $this->message->uid,
      ), WATCHDOG_ERROR);

      if ($this->plugin['options']['save on fail']) {
        $save = TRUE;
      }
    }
    elseif ($result && $this->plugin['options']['save on success']) {
      $save = TRUE;
    }

    return $save;
  }

  public function access() {
    return TRUE;
  }
}
