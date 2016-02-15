<?php
/**
 * @file
 * Contains \Drupal\message_notify\Tests\EmailNotifierTest.
 */

namespace Drupal\message_notify\Tests;

use Drupal\message\Entity\Message;
use Drupal\message\Entity\MessageType;
use Drupal\simpletest\WebTestBase;

/**
 * Test the email notifier plugin.
 *
 * @group message_notify
 *
 * Uses web test base since that provides the mock email handler.
 */
class EmailNotifierTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['message_notify_test'];

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
  public function setUp() {
    parent::setUp();

    $this->messageType = MessageType::load('message_notify_test');
    $this->messageNotifier = $this->container->get('message_notify.sender');
  }

  /**
   * Test that mails are properly sent.
   */
  public function testEmailNotifier() {
    $account = $this->drupalCreateUser();
    $message = Message::create(['type' => $this->messageType->id(), 'uid' => $account->id()]);
    $this->messageNotifier->send($message, [], 'email');
    $this->assertMail('subject', 'first partial', 'Expected email subject sent');
    $this->assertMail('body', "second partial\n\n", 'Expected email body sent');
  }

}
