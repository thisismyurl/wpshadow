<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Visual extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-visual_stability_trend', 'title' => __('Visual Stability Trend', 'wpshadow'), 'description' => __('Monitors layout shift patterns. Instability = poor UX signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}