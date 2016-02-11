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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    // Set some defaults.
    $configuration += [
      'save on success' => TRUE,
      'save on fail' => FALSE,
    ];
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\message_notify\Plugin\Notifier\MessageNotifyException
   */
  public function send() {
    $message = $this->message;
    $output = [];
    // @todo What to do about view modes?
    // foreach ($this->configuration['view_modes'] as $view_mode => $value) {
    $output['default'] = $message->getText();

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
    if (isset($this->configuration['rendered fields'])) {
      // @todo View mode support.
      foreach ($this->configuration['view_modes'] as $view_mode => $mode) {
        if (empty($options['rendered fields'][$view_mode])) {
          throw new MessageNotifyException('The rendered view mode "' . $view_mode . '" cannot be saved to field, as there is not a matching one.');
        }
        $field_name = $options['rendered fields'][$view_mode];

        // @todo Inject the content_type.manager if this check is needed.
        if (!$field = field_info_field($field_name)) {
          throw new MessageNotifyException('Field "' . $field_name . '"" does not exist.');
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
