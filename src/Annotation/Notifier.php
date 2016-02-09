<?php
/**
 * @file
 * Contains \Drupal\message_notify\Annotation\Notifier.
 */

namespace Drupal\message_notify\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a notifier plugin.
 *
 * @Annotation
 */
class Notifier extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable title.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

}
