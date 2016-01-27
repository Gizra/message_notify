<?php
/**
 * @file
 * Contains \Drupal\message_notify\Plugin\Notifier\MessageNotifierSMS.
 */

namespace Drupal\message_notify\Plugin\Notifier;

use Drupal\message_notify\Exception\MessageNotifyException;

/**
 * @file
 * SMS notifier.
 */
class MessageNotifierSMS extends MessageNotifierBase {

  /**
   * {@inheritdoc}
   */
  public function deliver(array $output = array()) {
    if (empty($this->message->smsNumber)) {
      // Try to get the SMS number from the account.
      $account = \Drupal::entityManager()->getStorage('user')->load($this->message->uid);
      if (!empty($account->sms_user['number'])) {
        $this->message->smsNumber = $account->sms_user['number'];
      }
    }

    if (empty($this->message->smsNumber)){
      throw new MessageNotifyException ('Message cannot be sent using SMS as the "smsNumber" property is missing from the Message entity or user entity.');
    }

    return sms_send($this->message->smsNumber, strip_tags($output['message_notify_sms_body']));
  }

}
