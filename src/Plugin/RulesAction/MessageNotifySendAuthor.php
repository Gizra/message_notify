<?php

namespace Drupal\message_notify\Plugin\RulesAction;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\message\MessageInterface;
use Drupal\message_notify\MessageNotifier;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MessageNotifySendAuthor.
 *
 * @package Drupal\message_notify\Plugin\RulesAction
 *
 * @RulesAction(
 *   id = "message_notify_send_author",
 *   label = @Translation("Send message to author"),
 *   category = @Translation("Message Notify"),
 *   context_definitions = {
 *     "message" = @ContextDefinition("entity:message",
 *       label = @Translation("Message"),
 *       description = @Translation("Specifies the message entity to send."),
 *       assignment_restriction = "selector"
 *     ),
 *   }
 * )
 */
class MessageNotifySendAuthor extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The message notifier service.
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  protected $messageNotifier;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\message_notify\MessageNotifier $message_notifier
   *   The message notifier service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessageNotifier $message_notifier) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messageNotifier = $message_notifier;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('message_notify.sender')
    );
  }

  /**
   * Executes the action with the given context.
   *
   * @param \Drupal\message\MessageInterface $message
   *   The node to modify.
   */
  protected function doExecute(MessageInterface $message) {
    // Send message to message creator.
    $this->messageNotifier->send($message);
  }

}
