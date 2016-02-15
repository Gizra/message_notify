<?php
/**
 * @file
 * Contains \Drupal\message_notify\Plugin\Notifier\MessageNotifierEmail.
 */

namespace Drupal\message_notify\Plugin\Notifier;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Email notifier.
 *
 * @Notifier(
 *   id = "email",
 *   title = @Translation("Email"),
 *   description = @Translation("Send messages via email"),
 *   view_modes = {
 *     "mail_title",
 *     "mail_body"
 *   }
 * )
 */
class MessageNotifierEmail extends MessageNotifierBase {

  /**
   * The mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs the email notifier plugin.
   *
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger, EntityTypeManagerInterface $entity_type_manager, MailManagerInterface $mail_manager) {
    // Set configuration defaults.
    $configuration += [
      'mail' => FALSE,
      'language override' => FALSE,
    ];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $entity_type_manager);

    $this->mailManager = $mail_manager;
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
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function deliver(array $output = []) {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->message->uid->entity;
    $mail = $this->configuration['mail'] ?: $account->getEmail();

    if (!$this->configuration['language override']) {
      $language = $account->getPreferredLangcode();
    }
    else {
      $language = $this->message->language()->getId();
    }

    // The subject in an email can't be with HTML, so strip it.
    // @todo Centralize rendering.
    $output['mail_title'] = strip_tags($output['mail_title']['#markup']);
    $output['mail_body'] = $output['mail_body']['#markup'];

    // Pass the message entity along to hook_drupal_mail().
    $output['message_entity'] = $this->message;

    $result =  $this->mailManager->mail(
      'message_notify',
      $this->message->getType()->id(),
      $mail,
      $language,
      $output
    );

    return $result['result'];
  }

}
