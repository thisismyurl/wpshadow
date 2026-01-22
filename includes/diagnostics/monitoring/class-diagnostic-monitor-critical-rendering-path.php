<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Critical extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-critical_rendering_path', 'title' => __('Critical Rendering Path Optimization', 'wpshadow'), 'description' => __('Analyzes which resources block rendering. Unoptimized = slower first paint.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
