<?php

namespace Drupal\Tests\message_notify_sms\Unit\Plugin\Notifier;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\sms\Exception\SmsDirectionException;
use Drupal\sms\Exception\RecipientRouteException;
use Drupal\sms\Provider\SmsProviderInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\message\MessageInterface;
use Drupal\message_notify_sms\Plugin\Notifier\Sms;
use Drupal\sms\Exception\NoPhoneNumberException;
use Drupal\sms\Provider\PhoneNumberProviderInterface;
use Drupal\Tests\UnitTestCase;
use Exception;
use Prophecy\Argument;

/**
 * Unit tests for the SMS notifier.
 *
 * @coversDefaultClass \Drupal\message_notify_sms\Plugin\Notifier\Sms
 *
 * @group message_notify_sms
 */
class SmsTest extends UnitTestCase {

  /**
   * Mocked configuration for entity delivery.
   *
   * @var array
   */
  protected $entityConfiguration;

  /**
   * Mocked configuration for number delivery.
   *
   * @var array
   */
  protected $numberConfiguration;

  /**
   * Mocked plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * Mocked plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Mocked logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Mocked renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Mocked phone number provider (entity).
   *
   * @var \Drupal\sms\Provider\PhoneNumberProviderInterface
   */
  protected $phoneNumberProvider;

  /**
   * Mocked phone number provider (number).
   *
   * @var \Drupal\sms\Provider\SmsProviderInterface
   */
  protected $smsProvider;

  /**
   * Rendered message.
   *
   * @var array
   */
  protected $output;

  /**
   * Test setup.
   */
  public function setUp() {
    parent::setUp();

    // SmsMessage.php#L326 is calling uuid service directly. Need to setup
    // fake container for this test.
    $container = new ContainerBuilder();
    $container->set('uuid', $this->getMockUuidService());
    \Drupal::setContainer($container);

    $this->entityConfiguration = [];
    $this->numberConfiguration = ['phone_number' => '18001234567'];
    $this->pluginId = $this->randomMachineName();
    $this->pluginDefinition = [
      'title' => $this->randomMachineName(),
      'viewModes' => [
        'sms_message',
      ],
    ];
    $this->logger = $this->prophesize(LoggerChannelInterface::class)->reveal();
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class)->reveal();
    $this->renderer = $this->prophesize(RendererInterface::class)->reveal();
    $this->phoneNumberProvider = $this->prophesize(PhoneNumberProviderInterface::class)->reveal();
    $this->smsProvider = $this->prophesize(SmsProviderInterface::class)->reveal();

    $this->output = ['sms_message' => 'Hello, World!'];
  }

  /**
   * Test Sms::deliver empty message.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverEmptyMessage() {
    $this->logger = $this->getMockLogger('warning');
    $notifier = $this->getEntityNotifier();

    $this->assertFalse($notifier->deliver(['sms_message' => '']));
  }

  /**
   * Test Sms::deliver entity with happy path.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverEntity() {
    $this->phoneNumberProvider = $this->getMockRespondingPhoneNumberProvider(FALSE);
    $this->logger = $this->getMockLogger('info');
    $notifier = $this->getEntityNotifier();

    $this->assertTrue($notifier->deliver($this->output));
  }

  /**
   * Test Sms::deliver entity that has no phone number.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverEntityNoPhoneNumber() {
    $this->phoneNumberProvider = $this->getMockThrowingPhoneProvider(NoPhoneNumberException::class);
    $this->logger = $this->getMockLogger('error');
    $notifier = $this->getEntityNotifier();

    $this->assertFalse($notifier->deliver($this->output));
  }

  /**
   * Test Sms::deliver entity with a random exception.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverEntityRandomException() {
    $this->phoneNumberProvider = $this->getMockThrowingPhoneProvider(Exception::class);
    $this->logger = $this->getMockLogger('error');
    $notifier = $this->getEntityNotifier();

    $this->assertFalse($notifier->deliver($this->output));
  }

  /**
   * Test Sms::deliver number happy path.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverNumber() {
    $this->logger = $this->getMockLogger('info');
    $this->smsProvider = $this->getMockRespondingSmsProvider(NULL);
    $notifier = $this->getNumberNotifier();

    $this->assertTrue($notifier->deliver($this->output));
  }

  /**
   * Test Sms::deliver number with message that has no direction.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverNumberNoDirection() {
    $this->logger = $this->getMockLogger('error');
    $this->smsProvider = $this->getMockThrowingSmsProvider(SmsDirectionException::class);
    $notifier = $this->getNumberNotifier();

    $this->assertFalse($notifier->deliver($this->output));
  }

  /**
   * Test Sms::deliver number with no gateway.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverNumberRouteException() {
    $this->logger = $this->getMockLogger('error');
    $this->smsProvider = $this->getMockThrowingSmsProvider(RecipientRouteException::class);
    $notifier = $this->getNumberNotifier();

    $this->assertFalse($notifier->deliver($this->output));
  }

  /**
   * Test Sms::deliver number with a random exception.
   *
   * @covers \Drupal\message_notify_sms\Plugin\Notifier\Sms::deliver
   */
  public function testDeliverNumberRandomException() {
    $this->logger = $this->getMockLogger('error');
    $this->smsProvider = $this->getMockThrowingSmsProvider(Exception::class);
    $notifier = $this->getNumberNotifier();

    $this->assertFalse($notifier->deliver($this->output));
  }

  /**
   * Returns a mocked logger expecting a single info call.
   *
   * @param string $level
   *   The log level.
   *
   * @return object
   *   The mock logger.
   */
  protected function getMockLogger($level) {
    $logger = $this->prophesize(LoggerChannelInterface::class);
    $logger
      ->$level(Argument::type('string'), Argument::type('array'))
      ->shouldBeCalledTimes(1);

    return $logger->reveal();
  }

  /**
   * Returns a mocked PhoneNumberProvider that returns the specified value.
   *
   * @param mixed $return_value
   *   The value PhoneNumberProvider::sendMessage should return.
   *
   * @return object
   *   The mock PhoneNumberProvider.
   */
  protected function getMockRespondingPhoneNumberProvider($return_value) {
    $phoneNumberProvider = $this->prophesize(PhoneNumberProviderInterface::class);
    $phoneNumberProvider
      ->sendMessage(Argument::type('Drupal\user\UserInterface'), Argument::type('Drupal\sms\Message\SmsMessageInterface'))
      ->willReturn($return_value);

    return $phoneNumberProvider->reveal();
  }

  /**
   * Returns a mocked PhoneNumberProvider that throws the specified exception.
   *
   * @param string $thrown_class
   *   The exception PhoneNumberProvider::sendMessage should throw.
   *
   * @return object
   *   The mock PhoneNumberProvider.
   */
  protected function getMockThrowingPhoneProvider($thrown_class) {
    $phoneNumberProvider = $this->prophesize(PhoneNumberProviderInterface::class);
    $phoneNumberProvider
      ->sendMessage(Argument::type('Drupal\user\UserInterface'), Argument::type('Drupal\sms\Message\SmsMessageInterface'))
      ->willThrow($thrown_class);

    return $phoneNumberProvider->reveal();
  }

  /**
   * Returns a mocked SmsProvider that returns the specified value.
   *
   * @param mixed $return_value
   *   The value SmsProvider::sendMessage should return.
   *
   * @return object
   *   The mock SmsProvider.
   */
  protected function getMockRespondingSmsProvider($return_value) {
    $smsProvider = $this->prophesize(SmsProviderInterface::class);
    $smsProvider
      ->queue(Argument::type('Drupal\sms\Message\SmsMessageInterface'))
      ->willReturn($return_value);

    return $smsProvider->reveal();
  }

  /**
   * Returns a mocked SmsProvider that throws the specified exception.
   *
   * @param string $thrown_class
   *   The exception SmsProvider::sendMessage should throw.
   *
   * @return object
   *   The mock SmsProvider.
   */
  protected function getMockThrowingSmsProvider($thrown_class) {
    $smsProvider = $this->prophesize(SmsProviderInterface::class);
    $smsProvider
      ->queue(Argument::type('Drupal\sms\Message\SmsMessageInterface'))
      ->willThrow($thrown_class);

    return $smsProvider->reveal();

  }

  /**
   * Returns a mocked User.
   *
   * @return object
   *   The mocked user.
   */
  protected function getMockUser() {
    $user = $this->prophesize(UserInterface::class);
    $user->id()->willReturn(42);
    $user->getAccountName()->willReturn('user');
    $user->getEmail()->willReturn('user@example.com');
    $user->getPreferredLangcode()->willReturn(Language::LANGCODE_DEFAULT);

    return $user->reveal();
  }

  /**
   * Returns a mocked Message.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object that owns the message.
   *
   * @return object
   *   The mocked Message
   */
  protected function getMockMessage(UserInterface $user) {
    $message = $this->prophesize(MessageInterface::class);
    $message->getOwner()->willReturn($user);
    $message->getOwnerId()->willReturn(42);

    return $message->reveal();
  }

  /**
   * Returns a mocked Notifier plugin tied to an entity.
   *
   * @return \Drupal\message_notify_sms\Plugin\Notifier\Sms
   *   The notifier instance.
   */
  protected function getEntityNotifier() {
    $user = $this->getMockUser();
    $message = $this->getMockMessage($user);

    return new Sms(
      $this->entityConfiguration,
      $this->pluginId,
      $this->pluginDefinition,
      $this->logger,
      $this->entityTypeManager,
      $this->renderer,
      $message,
      $this->phoneNumberProvider,
      $this->smsProvider
    );
  }

  /**
   * Returns a mocked Notifier plugin with manual options.
   *
   * @return \Drupal\message_notify_sms\Plugin\Notifier\Sms
   *   The notifier instance.
   */
  protected function getNumberNotifier() {
    return new Sms(
      $this->numberConfiguration,
      $this->pluginId,
      $this->pluginDefinition,
      $this->logger,
      $this->entityTypeManager,
      $this->renderer,
      NULL,
      $this->phoneNumberProvider,
      $this->smsProvider
    );
  }

  /**
   * Returns a mocked UUID service.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The mocked UUID service.
   */
  protected function getMockUuidService() {
    $uuid = $this->prophesize(UuidInterface::class);
    $uuid->generate(Argument::any())->willReturn(42);

    return $uuid;
  }

}
