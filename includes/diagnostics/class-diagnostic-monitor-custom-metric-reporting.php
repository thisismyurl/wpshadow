<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Custom-metric-reporting {
  public static function check() {
    return ['id' => 'monitor-custom_metric_reporting', 'title' => __('Custom Metric Accuracy', 'wpshadow'), 'description' => __('Verifies custom metrics tracking correctly. Inaccurate = wrong business decisions.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
