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
   * @param \Drupal\message\MessageInterface
   *   The message entity.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger, EntityTypeManagerInterface $entity_type_manager, MessageInterface $message) {
    // Set some defaults.
    $configuration += [
      'save on success' => TRUE,
      'save on fail' => FALSE,
    ];
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
    $this->message = $message;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MessageInterface $message = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.message_notify'),
      $container->get('entity_type.manager'),
      $message
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
      $this->logger->error('Could not send message using {title} to user ID {uid}.', ['{title}' => $this->pluginDefinition['title'], '{uid}' => $this->message->uid->entity->id()]);
      if ($this->configuration['save on fail']) {
        $save = TRUE;
      }
    }
    elseif ($result && $this->configuration['save on success']) {
      $save = TRUE;
    }

    if (isset($this->configuration['rendered fields'])) {
      foreach ($this->pluginDefinition['view_modes'] as $view_mode) {
        if (empty($this->configuration['rendered fields'][$view_mode])) {
          throw new MessageNotifyException('The rendered view mode "' . $view_mode . '" cannot be saved to field, as there is not a matching one.');
        }
        $field_name = $this->configuration['rendered fields'][$view_mode];

        // @todo Inject the content_type.manager if this check is needed.
        if (!$field = $this->entityTypeManager->getStorage('field_config')->load('message.' . $this->message->bundle() . '.' . $field_name)) {
          throw new MessageNotifyException('Field "' . $field_name . '"" does not exist.');
        }

        // Get the format from the field. We assume the first delta is the
        // same as the rest.
        if (!$format = $this->message->get($field_name)->format) {
          // Field has no formatting.
          // @todo Centralize/unify rendering.
          $this->message->set($field_name, $output[$view_mode]['#markup']);
        }
        else {
          $this->message->set($field_name, ['value' => $output[$view_mode], 'format' => $format]);
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

}
