# Message Notify SMS

Support sending of notifications over SMS

## Requirements

- [`message_notify`](https://www.drupal.org/project/message_notify)
- [`sms`](https://www.drupal.org/project/smsframework)

## Installation

- via admin: Go to `/admin/modules` and enable the module.
- via Drush: `drush en -y message_notify_sms`

## Configuration

- Go to `/admin/config/message/message-subscribe`
- Under "Default message notifiers", include `SMS` in your selection.
- Click "Save configuration".
