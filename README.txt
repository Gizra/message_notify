Message notify allows sending Messages (entities created with the Message
module).
Delivery can be done by email, or any other delivery method provided by
an implementing module.
Message notify uses "View modes", which allows you to customize which
message-text fields will be rendered and delivered.

To see it in action:
- Enable the Message-notify example module
- See how the message-text fields are assigned to the "Email subject" and
  "Email body" view modes
  in admin/structure/messages/manage/comment_insert/display/message_notify_email_subject
- Add a comment to a node, and an email will be sent to the node author