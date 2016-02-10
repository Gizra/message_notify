<?php
/**
 * @file
 * Contains \Drupal\message_notify\Plugin\Notifier\MessageNotifierBase.
 */

namespace Drupal\message_notify\Plugin\Notifier;

use Drupal\Component\Plugin\PluginBase;
use Drupal\message\MessageInterface;
use Drupal\message_notify\Exception\MessageNotifyException;

/**
 * An abstract implementation of MessageNotifierInterface.
 */
abstract class MessageNotifierBase extends PluginBase implements MessageNotifierInterface {

  /**
   * The message entity.
   *
   * @var \Drupal\message\MessageInterface
   */
  protected $message;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\message_notify\Plugin\Notifier\MessageNotifyException
   */
  public function send() {
    $message = $this->message;
    $output = [];
    foreach ($this->configuration['view_modes'] as $view_mode => $value) {
      $content = $message->buildContent($view_mode);
      $output[$view_mode] = render($content);
    }
    $result = $this->deliver($output);
    $this->postSend($result, $output);
    return $result;
  }

  /**
   * {@inheritdoc}
   *
   * - Save the rendered messages if needed.
   * - Invoke watchdog error on failure.
   */
  public function postSend($result, array $output = []) {
    $save = FALSE;
    if (!$result) {
      // @todo Inject logger.
      \Drupal::logger('message_notify')->error(t('Could not send message using @title to user ID @uid.'), ['@label' => $plugin['title'], '@uid' => $message->uid]);
      if ($this->configuration['save on fail']) {
        $save = TRUE;
      }
    }
    elseif ($result && $this->configuration['save on success']) {
      $save = TRUE;
    }

    // @todo Port this bit to 8.
    if ($options['rendered fields']) {
      // Save the rendered output into matching fields.
      $wrapper = entity_metadata_wrapper('message', $message);
      foreach ($this->plugin['view_modes'] as $view_mode => $mode) {
        if (empty($options['rendered fields'][$view_mode])) {
          throw new MessageNotifyException('The rendered view mode "' . $view_mode . '" cannot be saved to field, as there is not a matching one.');
        }
        $field_name = $options['rendered fields'][$view_mode];

        if (!$field = field_info_field($field_name)) {
          throw new MessageNotifyException(format_string('Field @field does not exist.', ['@field' => $field_name]));
        }

        // Get the format from the field. We assume the first delta is the
        // same as the rest.
        if (empty($wrapper->{$field_name}->format)) {
          $wrapper->{$field_name}->set($output[$view_mode]);
        }
        else {
          $format = $wrapper->type->{MESSAGE_FIELD_MESSAGE_TEXT}->get(0)->format->value();
          $wrapper->{$field_name}->set(['value' => $output[$view_mode], 'format' => $format]);
        }
      }
    }

    if ($save) {
      $this->message->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function init(MessageInterface $message) {
    $this->message = $message;
  }

}
