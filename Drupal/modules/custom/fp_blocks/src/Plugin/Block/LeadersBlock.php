<?php
namespace Drupal\fp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a Leaders block.
 *
 * @Block(
 *   id = "fp_leaders_block",
 *   admin_label = @Translation("Football Pools Leaders")
 * )
 */
class LeadersBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('Leaders will be displayed here. Placeholder content from the old PHP-Nuke block.'),
    ];
  }
}
