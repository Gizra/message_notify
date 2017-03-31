<?php

namespace Drupal\message_notify_sms\Plugin\Notifier;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\message\MessageInterface;
use Drupal\message_notify\Exception\MessageNotifyException;
use Drupal\message_notify\Plugin\Notifier\MessageNotifierBase;
use Drupal\sms\Direction;
use Drupal\sms\Exception\NoPhoneNumberException;
use Drupal\sms\Exception\RecipientRouteException;
use Drupal\sms\Message\SmsMessage;
use Drupal\sms\Message\SmsMessageInterface;
use Drupal\sms\Provider\PhoneNumberProviderInterface;
use Drupal\sms\Provider\SmsProviderInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SMS notifier.
 *
 * @Notifier(
 *   id = "sms",
 *   title = @Translation("SMS"),
 *   descriptions = @Translation("Send messages via SMS."),
 *   viewModes = {
 *     "sms_body"
 *   }
 * )
 */
class Sms extends MessageNotifierBase {

  /**
   * The SMS phone number provider service.
   *
   * @var \Drupal\sms\Provider\PhoneNumberProviderInterface
   */
  protected $phoneNumberProvider;

  /**
   * The SMS provider service.
   *
   * @var \Drupal\sms\Provider\SmsProviderInterface
   */
  protected $smsProvider;

  /**
   * Constructs the SMS notifier plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The message_notify logger channel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $render
   *   The rendering service.
   * @param \Drupal\message\MessageInterface $message
   *   (optional) The message entity. This is required when sending or
   *   delivering a notification. If not passed to the constructor, use
   *   ::setMessage().
   * @param \Drupal\sms\Provider\PhoneNumberProviderInterface $phone_number_provider
   *   The SMS phone number provider.
   * @param \Drupal\sms\Provider\SmsProviderInterface $sms_provider
   *   The SMS provider service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger, EntityTypeManagerInterface $entity_type_manager, RendererInterface $render, MessageInterface $message = NULL, PhoneNumberProviderInterface $phone_number_provider, SmsProviderInterface $sms_provider) {
    // Set configuration defaults.
    $configuration += [
      'mail' => FALSE,
      'language override' => FALSE,
    ];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $entity_type_manager, $render, $message);

    $this->phoneNumberProvider = $phone_number_provider;
    $this->smsProvider = $sms_provider;
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
      $container->get('renderer'),
      $message,
      $container->get('sms.phone_number'),
      $container->get('sms.provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function deliver(array $output = []) {
    $sms = new SmsMessage();
    $sms->setMessage(strip_tags($output['sms_body']));

    if (!empty($this->configuration['sms_number'])) {
      // Phone number is directly attached to the message entity.
      return $this->sendDirect($sms);
    }

    if (!$this->message->uid->entity instanceof UserInterface) {
      throw new MessageNotifyException('No account passed to the SMS notifier plugin.');
    }

    try {
      return (bool) $this->phoneNumberProvider->sendMessage($this->message->uid->entity, $sms);
    }
    catch (NoPhoneNumberException $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Send directly to a provided SMS number.
   *
   * @param \Drupal\sms\Message\SmsMessageInterface $sms
   *   The SMS message. Should already contain the body/payload.
   *
   * @return bool
   *   Returns TRUE if the message was succesfully added to the SMS queue.
   */
  protected function sendDirect(SmsMessageInterface $sms) {
    try {
      $sms->addRecipient($this->configuration['sms_number'])
        ->setDirection(Direction::OUTGOING);
      return (bool) $this->smsProvider->queue($sms);
    }
    catch (RecipientRouteException $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
