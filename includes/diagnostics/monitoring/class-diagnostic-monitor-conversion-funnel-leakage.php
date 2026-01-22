<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Conversion extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-conversion_funnel_leakage', 'title' => __('Conversion Funnel Leakage Detection', 'wpshadow'), 'description' => __('Identifies where users drop in funnel. Detects optimization opportunities.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
