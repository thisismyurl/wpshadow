<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Breadth extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-breadth_coverage', 'title' => __('Breadth Coverage Analysis', 'wpshadow'), 'description' => __('Monitors topic breadth across all content. Narrow coverage = lost long-tail traffic.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
