<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Nofollow extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-nofollow_balance', 'title' => __('Nofollow Link Balance', 'wpshadow'), 'description' => __('Monitors ratio of follow to nofollow links. Extreme imbalance = manipulative linking.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
