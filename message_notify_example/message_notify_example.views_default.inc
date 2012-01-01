<?php
/**
 * @file
 * message_notify_example.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function message_notify_example_views_default_views() {
  $export = array();

  $view = new view;
  $view->name = 'message_notify_example';
  $view->description = 'Show messages that were sent by email.';
  $view->tag = 'default';
  $view->base_table = 'message';
  $view->human_name = 'Message notify example';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Message notify example';
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '25';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['group_rendered'] = 1;
  $handler->display->display_options['style_options']['columns'] = array(
    'field_message_rendered_body' => 'field_message_rendered_body',
    'field_message_rendered_subject' => 'field_message_rendered_subject',
    'timestamp' => 'timestamp',
    'name' => 'name',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'field_message_rendered_body' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_message_rendered_subject' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'timestamp' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'name' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  $handler->display->display_options['style_options']['override'] = 1;
  $handler->display->display_options['style_options']['sticky'] = 0;
  $handler->display->display_options['style_options']['empty_table'] = 0;
  /* Relationship: Message: User uid */
  $handler->display->display_options['relationships']['user']['id'] = 'user';
  $handler->display->display_options['relationships']['user']['table'] = 'message';
  $handler->display->display_options['relationships']['user']['field'] = 'user';
  $handler->display->display_options['relationships']['user']['required'] = 0;
  /* Field: Message: Rendered body */
  $handler->display->display_options['fields']['field_message_rendered_body']['id'] = 'field_message_rendered_body';
  $handler->display->display_options['fields']['field_message_rendered_body']['table'] = 'field_data_field_message_rendered_body';
  $handler->display->display_options['fields']['field_message_rendered_body']['field'] = 'field_message_rendered_body';
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['external'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['alter']['html'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_body']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_body']['hide_empty'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['empty_zero'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_body']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_body']['field_api_classes'] = 0;
  /* Field: Message: Rendered subject */
  $handler->display->display_options['fields']['field_message_rendered_subject']['id'] = 'field_message_rendered_subject';
  $handler->display->display_options['fields']['field_message_rendered_subject']['table'] = 'field_data_field_message_rendered_subject';
  $handler->display->display_options['fields']['field_message_rendered_subject']['field'] = 'field_message_rendered_subject';
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['external'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['alter']['html'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_subject']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_subject']['hide_empty'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['empty_zero'] = 0;
  $handler->display->display_options['fields']['field_message_rendered_subject']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['field_message_rendered_subject']['field_api_classes'] = 0;
  /* Field: Message: Timestamp */
  $handler->display->display_options['fields']['timestamp']['id'] = 'timestamp';
  $handler->display->display_options['fields']['timestamp']['table'] = 'message';
  $handler->display->display_options['fields']['timestamp']['field'] = 'timestamp';
  $handler->display->display_options['fields']['timestamp']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['external'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['timestamp']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['timestamp']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['timestamp']['alter']['html'] = 0;
  $handler->display->display_options['fields']['timestamp']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['timestamp']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['timestamp']['hide_empty'] = 0;
  $handler->display->display_options['fields']['timestamp']['empty_zero'] = 0;
  $handler->display->display_options['fields']['timestamp']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['timestamp']['date_format'] = 'long';
  /* Field: User: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'users';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  $handler->display->display_options['fields']['name']['relationship'] = 'user';
  $handler->display->display_options['fields']['name']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['name']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['name']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['name']['alter']['external'] = 0;
  $handler->display->display_options['fields']['name']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['name']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['name']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['name']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['name']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['name']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['name']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['name']['alter']['html'] = 0;
  $handler->display->display_options['fields']['name']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['name']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['name']['hide_empty'] = 0;
  $handler->display->display_options['fields']['name']['empty_zero'] = 0;
  $handler->display->display_options['fields']['name']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['name']['link_to_user'] = 1;
  $handler->display->display_options['fields']['name']['overwrite_anonymous'] = 0;
  $handler->display->display_options['fields']['name']['format_username'] = 1;
  /* Sort criterion: Message: Message ID */
  $handler->display->display_options['sorts']['mid']['id'] = 'mid';
  $handler->display->display_options['sorts']['mid']['table'] = 'message';
  $handler->display->display_options['sorts']['mid']['field'] = 'mid';
  $handler->display->display_options['sorts']['mid']['order'] = 'DESC';
  /* Filter criterion: Message: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'message';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'comment_insert' => 'comment_insert',
  );

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'message-notify-example';
  $handler->display->display_options['menu']['type'] = 'normal';
  $handler->display->display_options['menu']['title'] = 'Message notify example';
  $handler->display->display_options['menu']['weight'] = '0';
  $export['message_notify_example'] = $view;

  return $export;
}
