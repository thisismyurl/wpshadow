<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Payback extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-payback_period', 'title' => __('Payback Period Monitoring', 'wpshadow'), 'description' => __('Tracks how long to break even on CAC. Lengthening = marketing efficiency decline.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}