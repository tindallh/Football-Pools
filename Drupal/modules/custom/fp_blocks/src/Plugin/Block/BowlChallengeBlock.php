<?php
namespace Drupal\fp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a Bowl Challenge block.
 *
 * @Block(
 *   id = "fp_bowl_challenge_block",
 *   admin_label = @Translation("Football Pools Bowl Challenge")
 * )
 */
class BowlChallengeBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('Bowl Challenge info will be displayed here. Placeholder content from the old PHP-Nuke block.'),
    ];
  }
}
