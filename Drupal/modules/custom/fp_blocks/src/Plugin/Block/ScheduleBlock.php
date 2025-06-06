<?php
namespace Drupal\fp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a Schedule block.
 *
 * @Block(
 *   id = "fp_schedule_block",
 *   admin_label = @Translation("Football Pools Schedule")
 * )
 */
class ScheduleBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('Schedule will be displayed here. This code is a placeholder ported from the old PHP-Nuke block.'),
    ];
  }
}
