<?php
/**
 * @file
 * Contains \Drupal\message_notify\Plugin\Notifier\MessageNotifierBase.
 */

namespace Drupal\message_notify\Plugin\Notifier;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\message\MessageInterface;
use Drupal\message_notify\Exception\MessageNotifyException;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The message_notify logger channel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger, EntityTypeManagerInterface $entity_type_manager) {
    // Set some defaults.
    $configuration += [
      'save on success' => TRUE,
      'save on fail' => FALSE,
    ];
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.message_notify'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function send() {
    $output = [];

    $view_builder = $this->entityTypeManager->getViewBuilder('message');
    foreach ($this->pluginDefinition['view_modes'] as $view_mode) {
      $output[$view_mode] = $view_builder->view($this->message, $view_mode);
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
      $this->logger->error('Could not send message using {title} to user ID {uid}.', ['{title}' => $this->pluginDefinition['title'], '{uid}' => $message->uid->entity->id()]);
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
