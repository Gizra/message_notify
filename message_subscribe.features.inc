<?php
/**
 * @file
 * message_subscribe.features.inc
 */

/**
 * Implements hook_flag_default_flags().
 */
function message_subscribe_flag_default_flags() {
  $flags = array();
  // Exported flag: "Content".
  $flags['subscribe_node'] = array(
    'content_type' => 'node',
    'title' => 'Content',
    'global' => '0',
    'types' => array(),
    'flag_short' => 'Subscribe',
    'flag_long' => '',
    'flag_message' => 'You are now subscribed to this item.',
    'unflag_short' => 'Unsubscribe',
    'unflag_long' => '',
    'unflag_message' => 'You are no longer subscribed to this item.',
    'unflag_denied_text' => '',
    'link_type' => 'toggle',
    'roles' => array(
      'flag' => array(
        0 => '2',
      ),
      'unflag' => array(
        0 => '2',
      ),
    ),
    'weight' => 0,
    'show_on_form' => 0,
    'access_author' => '',
    'show_on_page' => 1,
    'show_on_teaser' => 1,
    'show_contextual_link' => 0,
    'i18n' => 0,
    'module' => 'message_subscribe',
    'locked' => array(
      0 => 'name',
    ),
    'api_version' => 2,
  );
  // Exported flag: "Terms".
  $flags['subscribe_term'] = array(
    'content_type' => 'taxonomy_term',
    'title' => 'Terms',
    'global' => '0',
    'types' => array(),
    'flag_short' => 'Subscribe',
    'flag_long' => '',
    'flag_message' => 'You are now subscribed to this item.',
    'unflag_short' => 'Unsubscribe',
    'unflag_long' => '',
    'unflag_message' => 'You are no longer subscribed to this item.',
    'unflag_denied_text' => '',
    'link_type' => 'toggle',
    'roles' => array(
      'flag' => array(
        0 => '2',
      ),
      'unflag' => array(
        0 => '2',
      ),
    ),
    'weight' => 0,
    'show_on_entity' => TRUE,
    'show_on_form' => 0,
    'access_author' => '',
    'module' => 'message_subscribe',
    'locked' => array(
      0 => 'name',
    ),
    'api_version' => 2,
  );
  // Exported flag: "Users".
  $flags['subscribe_user'] = array(
    'content_type' => 'user',
    'title' => 'Users',
    'global' => '0',
    'types' => array(),
    'flag_short' => 'Subscribe',
    'flag_long' => '',
    'flag_message' => 'You are now subscribed to this item.',
    'unflag_short' => 'Unsubscribe',
    'unflag_long' => '',
    'unflag_message' => 'You are no longer subscribed to this item.',
    'unflag_denied_text' => '',
    'link_type' => 'toggle',
    'roles' => array(
      'flag' => array(
        0 => '2',
      ),
      'unflag' => array(
        0 => '2',
      ),
    ),
    'weight' => 0,
    'show_on_form' => 0,
    'access_author' => '',
    'show_on_profile' => TRUE,
    'access_uid' => '',
    'module' => 'message_subscribe',
    'locked' => array(
      0 => 'name',
    ),
    'api_version' => 2,
  );
  return $flags;

}
