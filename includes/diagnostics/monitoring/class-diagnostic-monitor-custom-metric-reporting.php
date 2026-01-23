<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Custom extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-custom_metric_reporting', 'title' => __('Custom Metric Accuracy', 'wpshadow'), 'description' => __('Verifies custom metrics tracking correctly. Inaccurate = wrong business decisions.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}