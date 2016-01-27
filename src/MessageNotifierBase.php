<?php
namespace Drupal\message_notify;

/**
 * An abstract implementation of MessageNotifierInterface.
 */
abstract class MessageNotifierBase implements MessageNotifierInterface {

  /**
   * The plugin definition.
   */
  protected $plugin;

  /**
   * The message entity.
   */
  protected $message;

  public function __construct($plugin, Message $message) {
    $this->plugin = $plugin;
    $this->message = $message;
  }

  public function send() {
    $message = $this->message;
    $output = array();
    foreach ($this->plugin['view_modes'] as $view_mode => $value) {
      $content = $message->buildContent($view_mode);
      $output[$view_mode] = render($content);
    }
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
    $plugin = $this->plugin;
    $message = $this->message;

    $options = $plugin['options'];

    $save = FALSE;
    if (!$result) {
      \Drupal::logger('message_notify')->error(t('Could not send message using @title to user ID @uid.'), array('@label' => $plugin['title'], '@uid' => $message->uid));
      if ($options['save on fail']) {
        $save = TRUE;
      }
    }
    elseif ($result && $options['save on success']) {
      $save = TRUE;
    }

    if ($options['rendered fields']) {
      // Save the rendered output into matching fields.
      $wrapper = entity_metadata_wrapper('message', $message);
      foreach ($this->plugin['view_modes'] as $view_mode => $mode) {
        if (empty($options['rendered fields'][$view_mode])) {
          throw new MessageNotifyException(format_string('The rendered view mode @mode cannot be saved to field, as there is not a matching one.', array('@mode' => $mode['label'])));
        }
        $field_name = $options['rendered fields'][$view_mode];

        if (!$field = field_info_field($field_name)) {
          throw new MessageNotifyException(format_string('Field @field does not exist.', array('@field' => $field_name)));
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

    if ($save) {
      $message->save();
    }
  }

  public function access() {
    return TRUE;
  }
}
