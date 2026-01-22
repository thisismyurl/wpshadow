<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Utm-parameter-consistency {
  public static function check() {
    return ['id' => 'monitor-utm_parameter_consistency', 'title' => __('UTM Parameter Consistency', 'wpshadow'), 'description' => __('Monitors UTM naming conventions. Inconsistency = analytics mess.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
