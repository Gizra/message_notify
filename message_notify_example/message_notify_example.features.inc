<?php
/**
 * @file
 * message_notify_example.features.inc
 */

/**
 * Implements hook_views_api().
 */
function message_notify_example_views_api() {
  list($module, $api) = func_get_args();
  if ($module == "views" && $api == "views_default") {
    return array("version" => "3.0");
  }
}

/**
 * Implements hook_default_message_type().
 */
function message_notify_example_default_message_type() {
  $items = array();
  $items['comment_insert'] = entity_import('message_type', '{
    "name" : "comment_insert",
    "description" : "Comment insert",
    "argument_keys" : [
      "@comment-author",
      "@comment-author-url",
      "@node-url",
      "@node-title",
      "@node-author"
    ],
    "category" : "message_type_email",
    "message_text" : { "und" : [
        {
          "value" : "Hello @node-author,\\r\\n\\r\\n\\u003ca href=\\"@comment-author-url\\"\\u003e@comment-author\\u003c\\/a\\u003e has commented on your post \\u003ca href=\\"@node-url\\"\\u003e@node-title\\u003c\\/a\\u003e.\\r\\n\\r\\nCheers,\\r\\nMessage notify robot\\r\\n\\r\\n",
          "format" : "filtered_html",
          "safe_value" : "\\u003cp\\u003eHello @node-author,\\u003c\\/p\\u003e\\n\\u003cp\\u003e\\u003ca href=\\"@comment-author-url\\"\\u003e@comment-author\\u003c\\/a\\u003e has commented on your post \\u003ca href=\\"@node-url\\"\\u003e@node-title\\u003c\\/a\\u003e.\\u003c\\/p\\u003e\\n\\u003cp\\u003eCheers,\\u003cbr \\/\\u003e\\nMessage notify robot\\u003c\\/p\\u003e\\n"
        }
      ]
    },
    "message_text_subject" : { "und" : [
        {
          "value" : "@comment-author commented on @node-title",
          "format" : null,
          "safe_value" : "@comment-author commented on @node-title"
        }
      ]
    },
    "rdf_mapping" : []
  }');
  return $items;
}
