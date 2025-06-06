<?php
namespace Drupal\fp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a Coaches block.
 *
 * @Block(
 *   id = "fp_coaches_block",
 *   admin_label = @Translation("Football Pools Coaches")
 * )
 */
class CoachesBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('Coaches will be displayed here. Placeholder content from the old PHP-Nuke block.'),
    ];
  }
}
