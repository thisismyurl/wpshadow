<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Visual-stability-trend {
  public static function check() {
    return ['id' => 'monitor-visual_stability_trend', 'title' => __('Visual Stability Trend', 'wpshadow'), 'description' => __('Monitors layout shift patterns. Instability = poor UX signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
