<?php

/**
 * @file
 * Slack notifier.
 */

class MessageNotifierSlack extends MessageNotifierBase {

  public function deliver(array $output = array()) {

    if (!$webhook_url = slack_get_default_webhook_url()){
      throw new MessageNotifyException('Message cannot be sent if Webhook URL is not configured in Slack module settings.');
    }

    return slack_send_message($webhook_url, $output['message_notify_slack_body']);
  }
}
