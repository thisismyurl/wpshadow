<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Payback-period {
  public static function check() {
    return ['id' => 'monitor-payback_period', 'title' => __('Payback Period Monitoring', 'wpshadow'), 'description' => __('Tracks how long to break even on CAC. Lengthening = marketing efficiency decline.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
