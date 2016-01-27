<?php
/**
 * @file
 * Contains \Drupal\message_notify\Plugin\Notifier\MessageNotifierEmail.
 */

namespace Drupal\message_notify\Plugin\Notifier;

/**
 * Email notifier.
 *
 * @Notifier(
 *   id = "email",
 *   title = @Translation("E-mail notifier")
 * )
 */
class MessageNotifierEmail extends MessageNotifierBase {

  /**
   * {@inheritdoc}
   */
  public function deliver(array $output = array()) {
    $plugin = $this->plugin;
    $message = $this->message;

    $options = $plugin['options'];

    $account = \Drupal::entityManager()->getStorage('user')->load($message->uid);
    $mail = $options['mail'] ? $options['mail'] : $account->mail;

    $languages = language_list();
    if (!$options['language override']) {
      $lang = !empty($account->language) && $account->language != \Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED ? $languages[$account->language]: language_default();
    }
    else {
      $lang = $languages[$message->language];
    }

    // The subject in an email can't be with HTML, so strip it.
    $output['message_notify_email_subject'] = strip_tags($output['message_notify_email_subject']);

    // Pass the message entity along to hook_drupal_mail().
    $output['message_entity'] = $message;

    $result =  drupal_mail('message_notify', $message->type, $mail, $lang, $output);
    return $result['result'];
  }

}
