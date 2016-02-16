<?php
/**
 * @file
 * Contains \Drupal\message_notify\Tests\MessageNotifyTest.
 */

namespace Drupal\message_notify\Tests;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\message\Entity\Message;
use Drupal\message\Entity\MessageType;
use Drupal\message_notify\Exception\MessageNotifyException;
use Drupal\simpletest\KernelTestBase;
use Drupal\user\Entity\User;

/**
 * Test the Message notifier plugins handling.
 *
 * @group message_notify
 */
class MessageNotifyTest extends KernelTestBase {

  /**
   * Testing message type.
   *
   * @var \Drupal\message\MessageTypeInterface
   */
  protected $messageType;

  /**
   * The message notification service.
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  protected $messageNotifier;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'message_notify_test',
    'message_notify',
    'message',
    'user',
    'system',
    'field',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('message_type');
    $this->installEntitySchema('message');
    $this->installConfig(['message', 'message_notify', 'message_notify_test']);
    $this->installSchema('system', ['sequences']);

    $this->messageType = MessageType::load('message_notify_test');

    $this->messageNotifier = $this->container->get('message_notify.sender');
  }

  /**
   * Test send method.
   *
   * Check the correct info is sent to delivery.
   */
  public function testDeliver() {
    $message = Message::create(['type' => $this->messageType->id()]);
    $message->message_text_another = 'another field';
    $this->messageNotifier->send($message, [], 'test');

    // The test notifier added the output to the message.
    $output = $message->output;

    $text = $message->getText();
    // @todo Should this be rendered in the notifier plugin?
    $this->assertTrue(isset($output['foo']['message_text_another']));
    $this->assertEqual($text[1], $output['foo']['#markup']);
    $this->assertFalse(isset($output['bar']['message_text_another']));
    $this->assertEqual($text[0], $output['bar']['#markup']);
  }

  /**
   * Test Message save on delivery.
   */
  public function testPostSendMessageSave() {
    $account = User::create(['name' => $this->randomMachineName()]);
    $account->save();
    $message = Message::create(['type' => $this->messageType->id(), 'uid' => $account->id()]);
    $message->fail = FALSE;
    $this->messageNotifier->send($message, [], 'test');
    $this->assertTrue($message->id(), 'Message saved after successful delivery.');

    $message = Message::create(['type' => $this->messageType->id(), 'uid' => $account->id()]);
    $message->fail = TRUE;
    $this->messageNotifier->send($message, [], 'test');
    $this->assertFalse($message->id(), 'Message not saved after unsuccessful delivery.');

    // Disable saving Message on delivery.
    $options = [
      'save on fail' => FALSE,
      'save on success' => FALSE,
    ];

    $message = Message::create(['type' => $this->messageType->id(), 'uid' => $account->id()]);
    // @todo See above.
    $message->fail = FALSE;
    $this->messageNotifier->send($message, $options, 'test');
    $this->assertTrue($message->isNew(), 'Message not saved after successful delivery.');

    $message = Message::create(['type' => $this->messageType->id(), 'uid' => $account->id()]);
    $message->fail = TRUE;
    $this->messageNotifier->send($message, $options, 'test');
    $this->assertTrue($message->isNew(), 'Message not saved after unsuccessful delivery.');
  }

  /**
   * Test populating the rednered output to fields.
   */
  public function testPostSendRenderedField() {
    $this->attachRenderedFields();

    // Test plain fields.
    $options = [
      'rendered fields' => [
        'foo' => 'rendered_foo',
        'bar' => 'rendered_bar',
      ],
    ];

    $message = Message::create(['type' => $this->messageType->id()]);
    $this->messageNotifier->send($message, $options, 'test');
    $this->assertTrue($message->rendered_foo->value && $message->rendered_bar->value, 'Message is rendered to fields.');

    // Test field with text-processing.
    $options = [
      'rendered fields' => [
        'foo' => 'rendered_baz',
        'bar' => 'rendered_bar',
      ],
    ];

    $message = Message::create(['type' => $this->messageType->id()]);
    $this->messageNotifier->send($message, $options, 'test');
    $this->assertTrue($message->rendered_baz->value && $message->rendered_bar->value, 'Message is rendered to fields with text-processing.');

    // Test missing view mode key in the rendered fields.
    $options = [
      'rendered fields' => [
        'foo' => 'rendered_foo',
        // No "bar" field.
      ],
    ];
    $message = Message::create(['type' => $this->messageType->id()]);
    try {
      $this->messageNotifier->send($message, $options, 'test');
      $this->fail('Can save rendered message with missing view mode.');
    }
    catch (MessageNotifyException $e) {
      $this->pass('Cannot save rendered message with missing view mode.');
    }

    // Test invalid field name.
    $options = [
      'rendered fields' => [
        'foo' => 'wrong_field',
        'bar' => 'rendered_bar',
      ],
    ];
    $message = Message::create(['type' => $this->messageType->id()]);
    try {
      $this->messageNotifier->send($message, $options, 'test');
      $this->fail('Can save rendered message to non-existing field.');
    }
    catch (MessageNotifyException $e) {
      $this->pass('Cannot save rendered message to non-existing field.');
    }
  }

  /**
   * Helper function to attach rendred fields.
   *
   * @see MessageNotifyNotifier::testPostSendRenderedField()
   */
  function attachRenderedFields() {
    foreach (['rendered_foo', 'rendered_bar', 'rendered_baz'] as $field_name) {
      // Use formatted text for `baz`, plain for others.
      $config = [
        'field_name' => $field_name,
        'type' => 'string_long',
        'entity_type' => 'message',
      ];
      if ($field_name == 'rendered_baz') {
        $config['type'] = 'text_long';
      }
      $field_storage = FieldStorageConfig::create($config);
      $field_storage->save();

      $field = FieldConfig::create([
        'field_name' => $field_name,
        'bundle' => $this->messageType->id(),
        'entity_type' => 'message',
        'label' => $field_name,
      ]);

      $field->save();
    }
  }

}
