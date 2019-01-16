<?php

namespace Drupal\Tests\message_notify_sms\Kernel;

use Drupal\Core\Entity\EntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\message\Entity\Message;
use Drupal\message\Entity\MessageTemplate;
use Drupal\user\Entity\User;

/**
 * Test the Message notifier plugins handling.
 *
 * @group message_notify
 */
class MessageNotifySmsTest extends KernelTestBase {

  /**
   * Testing message template.
   *
   * @var \Drupal\message\MessageTemplateInterface
   */
  protected $messageTemplate;

  /**
   * The message notification service.
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  protected $messageNotifier;

  /**
   * The sample message.
   *
   * @var string
   */
  protected $messageText = 'Hello, World!';

  /**
   * The sample number.
   *
   * @var string
   */
  protected $messageNumber = '18001234567';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'field',
    'text',
    'user',
    'telephone',
    'dynamic_entity_reference',
    'sms',
    'message',
    'message_notify',
    'message_notify_sms',
    'message_notify_sms_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('message');
    $this->installEntitySchema('phone_number_settings');
    $this->installEntitySchema('sms_phone_number_verification');
    $this->installEntitySchema('sms_report');
    $this->installEntitySchema('sms_gateway');
    $this->installEntitySchema('sms');
    $this->installEntitySchema('sms_result');
    $this->installConfig([
      'sms',
      'message',
      'message_notify',
      'message_notify_sms',
      'message_notify_sms_test',
    ]);

    $this->messageTemplate = MessageTemplate::load('message_notify_sms_test');

    $this->messageNotifier = $this->container->get('message_notify.sender');
  }

  /**
   * Tests notifier sending a message to an entity.
   */
  public function testSendEntity() {
    // Create an active user with a phone number.
    $user = User::create([
      'name' => $this->randomMachineName(),
      'field_sms' => $this->messageNumber,
      'status' => 1,
    ]);

    $user->save();

    $this->verifyEntityPhoneNumber($user);

    $message = $this->createEntityMessage($user);
    $result = $this->messageNotifier->send($message, [], 'sms');

    $this->assertTrue($result);
  }

  /**
   * Tests notifier sending a message to an inactive entity.
   */
  public function testSendEntityInactive() {
    // Create an active user with a phone number.
    $user = User::create([
      'name' => $this->randomMachineName(),
      'field_sms' => $this->messageNumber,
    ]);

    $user->save();

    $this->verifyEntityPhoneNumber($user);

    $message = $this->createEntityMessage($user);
    $result = $this->messageNotifier->send($message, [], 'sms');

    $this->assertFalse($result);
  }

  /**
   * Tests notifier sending a message to an entity with no phone number.
   */
  public function testSendEntityNoNumber() {
    // Create an active user with a phone number.
    $user = User::create([
      'name' => $this->randomMachineName(),
      'status' => 1,
    ]);

    $user->save();

    $message = $this->createEntityMessage($user);
    $result = $this->messageNotifier->send($message, [], 'sms');

    $this->assertFalse($result);
  }

  /**
   * Tests notifier sending a message to an unverified entity.
   */
  public function testSendEntityUnverified() {
    // Create an active user with a phone number.
    $user = User::create([
      'name' => $this->randomMachineName(),
      'field_sms' => $this->messageNumber,
      'status' => 1,
    ]);

    $user->save();

    $message = $this->createEntityMessage($user);
    $result = $this->messageNotifier->send($message, [], 'sms');

    $this->assertFalse($result);
  }

  /**
   * Tests notifier sending a message to a number.
   */
  public function testSendNumber() {
    $message = Message::create([
      'template' => $this->messageTemplate->id(),
      'message_notify_sms_test_text' => $this->messageText,
    ]);

    $result = $this->messageNotifier->send($message, ['phone_number' => $this->messageNumber], 'sms');

    $this->assertTrue($result);
  }

  /**
   * Creates a Message object owned by the supplied entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that owns the message.
   *
   * @return \Drupal\message\MessageInterface
   *   The message object.
   */
  protected function createEntityMessage(EntityInterface $entity) {
    return Message::create([
      'uid' => $entity->id(),
      'template' => $this->messageTemplate->id(),
      'message_notify_sms_test_text' => $this->messageText,
    ]);
  }

  /**
   * Verify an entity's phone number, if one exists.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose phone number to verify.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function verifyEntityPhoneNumber(EntityInterface $entity) {
    $verifications = \Drupal::entityTypeManager()
      ->getStorage('sms_phone_number_verification')
      ->loadByProperties([
        'entity__target_type' => $entity->getEntityTypeId(),
        'entity__target_id' => $entity->id(),
        'phone' => $entity->field_sms->value,
      ]);
    $verification = reset($verifications);
    $verification->setStatus(TRUE)->save();
  }

}
