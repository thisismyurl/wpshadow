<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Keyword-density-monitoring {
  public static function check() {
    return ['id' => 'monitor-keyword_density_monitoring', 'title' => __('Keyword Density Monitoring', 'wpshadow'), 'description' => __('Tracks keyword frequency naturally. High density = over-optimization penalty risk.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
