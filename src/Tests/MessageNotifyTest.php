<?php
/**
 * @file
 * Contains \Drupal\message_notify\Tests\MessageNotifyTest.
 */

namespace Drupal\message_notify\Tests;

use Drupal\message\Entity\Message;
use Drupal\message\Entity\MessageType;
use Drupal\simpletest\WebTestBase;

/**
 * Test the Message notifier plugins handling.
 *
 * @group message_notify
 */
class MessageNotifyTest extends WebTestBase {

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
  public static $modules = ['message_notify_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

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

    $this->assertEqual($output['foo'], $message->get('text')->get(0)->getValue());
    $this->assertEqual($output['bar'], $message->get('message_text_another')->get(0)->getValue());
    // @todo 7.x was expecting an array keyed by view mode. 8.x is a string.
    // $this->assertEqual($output['foo'], $wrapper->{MESSAGE_FIELD_MESSAGE_TEXT}->get(1)->value->value(), 'Correct values rendered in first view mode.');
    // $this->assertEqual($output['bar'], $wrapper->message_text_another->value(), 'Correct values rendered in second view mode.');
  }

  /**
   * Test Message save on delivery.
   */
  public function testPostSendMessageSave() {
    $message = Message::create(['type' => $this->messageType->id()]);
    $message->fail = FALSE;
    $this->messageNotifier->send($message, [], 'test');
    $this->assertTrue($message->id(), 'Message saved after successful delivery.');

    $message = Message::create(['type' => $this->messageType->id()]);
    $message->fail = TRUE;
    $this->messageNotifier->send($message, [], 'test');
    $this->assertFalse($message->id(), 'Message not saved after unsuccessful delivery.');

    // Disable saving Message on delivery.
    $options = [
      'save on fail' => FALSE,
      'save on success' => FALSE,
    ];

    $message = Message::create(['type' => $this->messageType->id()]);
    // @todo See above.
    $message->fail = FALSE;
    $this->messageNotifier->send($message, $options, 'test');
    $this->assertTrue($message->isNew(), 'Message not saved after successful delivery.');

    $message = Message::create(['type' => $this->messageType->id()]);
    $message->fail = TRUE;
    $this->messageNotifier->send($message, $options, 'test');
    $this->assertTrue($message->isNew(), 'Message not saved after unsuccessful delivery.');
  }

  /**
   * Test populating the rednered output to fields.
   */
  function testPostSendRenderedField() {
    // @todo Fix this test.
    $this->fail('Test not ported to 8.x');
    return;
    $this->attachRenderedFields();

    // Test plain fields.
    $options = array(
      'rendered fields' => array(
        'foo' => 'rendered_foo',
        'bar' => 'rendered_bar',
      ),
    );
    $message = message_create('foo');
    message_notify_send_message($message, $options, 'test');
    $wrapper = entity_metadata_wrapper('message', $message);
    $this->assertTrue($wrapper->rendered_foo->value() && $wrapper->rendered_bar->value(), 'Message is rendered to fields.');

    // Test field with text-processing.
    $options = array(
      'rendered fields' => array(
        'foo' => 'rendered_baz',
        'bar' => 'rendered_bar',
      ),
    );
    $message = message_create('foo');
    message_notify_send_message($message, $options, 'test');
    $wrapper = entity_metadata_wrapper('message', $message);
    $this->assertTrue($wrapper->rendered_baz->value->value() && $wrapper->rendered_bar->value(), 'Message is rendered to fields with text-processing.');

    // Test missing view mode key in the rendered fields.
    $options = array(
      'rendered fields' => array(
        'foo' => 'rendered_foo',
        // No "bar" field.
      ),
    );
    $message = message_create('foo');
    try {
      message_notify_send_message($message, $options, 'test');
      $this->fail('Can save rendered message with missing view mode.');
    }
    catch (MessageNotifyException $e) {
      $this->pass('Cannot save rendered message with missing view mode.');
    }

    // Test invalid field name.
    $options = array(
      'rendered fields' => array(
        'foo' => 'wrong_field',
        'bar' => 'rendered_bar',
      ),
    );
    $message = message_create('foo');
    try {
      message_notify_send_message($message, $options, 'test');
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
    foreach (array('rendered_foo', 'rendered_bar', 'rendered_baz') as $field_name) {
      $field = array(
        'field_name' => $field_name,
        'type' => 'text_long',
        'entity_types' => array('message'),
      );
      // @FIXME
// Fields and field instances are now exportable configuration entities, and
// the Field Info API has been removed.
// 
// 
// @see https://www.drupal.org/node/2012896
// $field = field_create_field($field);

      $instance = array(
        'field_name' => $field_name,
        'bundle' => 'foo',
        'entity_type' => 'message',
        'label' => $field_name,
      );

      if ($field_name == 'rendered_baz') {
        $instance['settings'] = array(
          'text_processing' => 1,
        );
      }
      // @FIXME
// Fields and field instances are now exportable configuration entities, and
// the Field Info API has been removed.
// 
// 
// @see https://www.drupal.org/node/2012896
// field_create_instance($instance);

    }
  }
}

