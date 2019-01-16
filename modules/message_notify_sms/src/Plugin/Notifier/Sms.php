<?php

namespace Drupal\message_notify_sms\Plugin\Notifier;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\message\MessageInterface;
use Drupal\message_notify\Plugin\Notifier\MessageNotifierBase;
use Drupal\sms\Direction;
use Drupal\sms\Message\SmsMessage;
use Drupal\sms\Provider\PhoneNumberProviderInterface;
use Drupal\sms\Provider\SmsProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SMS notifier.
 *
 * @Notifier(
 *   id = "sms",
 *   title = @Translation("SMS"),
 *   description = @Translation("Send messages via sms"),
 *   viewModes = {
 *     "sms_message"
 *   }
 * )
 */
class Sms extends MessageNotifierBase {

  /**
   * The SMS provider for entities.
   *
   * @var \Drupal\sms\Provider\PhoneNumberProviderInterface
   */
  protected $phoneNumberProvider;

  /**
   * The SMS provider for raw phone numbers.
   *
   * @var \Drupal\sms\Provider\SmsProviderInterface
   */
  protected $smsProvider;

  /**
   * Constructs the plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The message_notify_sms logger channel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The rendering service.
   * @param \Drupal\message\MessageInterface $message
   *   (optional) The message entity. This is required when sending or
   *   delivering a notification. If not passed to the constructor, use
   *   ::setMessage().
   * @param \Drupal\sms\Provider\PhoneNumberProviderInterface $phone_number_provider
   *   The sms provider (for entities).
   * @param \Drupal\sms\Provider\SmsProviderInterface $sms_provider
   *   The sms provider (for phone numbers).
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, MessageInterface $message = NULL, PhoneNumberProviderInterface $phone_number_provider, SmsProviderInterface $sms_provider) {
    // Set configuration defaults.
    $configuration = $configuration + ['phone_number' => FALSE];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $entity_type_manager, $renderer, $message);

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
    $message = trim(strip_tags($output['sms_message']));

    if ($message === '') {
      $this->logger->warning('Failed to send SMS. Message empty.', []);
      return FALSE;
    }

    try {
      $phone_number = $this->configuration['phone_number'];

      if ($phone_number) {
        $this->sendToNumber($message, $phone_number);

        $this->logger->info('Queued SMS for {phone_number}.', [
          'phone_number' => $phone_number,
        ]);
      }
      else {
        $user = $this->message->getOwner();
        $this->sendToEntity($message, $user);

        $this->logger->info('Queued SMS for {name} (uid: {id}).', [
          'id' => $user->id(),
          'name' => $user->getAccountName(),
        ]);
      }

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to send SMS.', [
        'exception' => $e,
      ]);
      return FALSE;
    }
  }

  /**
   * Send message to a phone number.
   *
   * @param string $message
   *   The rendered message.
   * @param string $number
   *   The recepient's phone number.
   *
   * @throws \Drupal\sms\Exception\SmsDirectionException
   *   Thrown if no direction is set for the message.
   * @throws \Drupal\sms\Exception\RecipientRouteException
   *   Thrown if no gateway could be determined for the message.
   */
  protected function sendToNumber($message, $number) {
    $sms_message = (new SmsMessage())
      ->setMessage($message)
      ->addRecipient($number)
      ->setDirection(Direction::OUTGOING);

    $this->smsProvider->queue($sms_message);
  }

  /**
   * Sends message to entity's configured phone number.
   *
   * @param string $message
   *   The rendered message.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The receiving entity.
   *
   * @throws \Drupal\sms\Exception\NoPhoneNumberException
   *   Thrown if entity does not have a phone number.
   */
  protected function sendToEntity($message, EntityInterface $entity) {
    $sms_message = (new SmsMessage())
      ->setMessage($message);

    $this->phoneNumberProvider->sendMessage($entity, $sms_message);
  }

}
