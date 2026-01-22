<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Conversion-funnel-leakage {
  public static function check() {
    return ['id' => 'monitor-conversion_funnel_leakage', 'title' => __('Conversion Funnel Leakage Detection', 'wpshadow'), 'description' => __('Identifies where users drop in funnel. Detects optimization opportunities.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
