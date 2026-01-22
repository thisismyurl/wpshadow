<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Rendering extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-rendering_performance', 'title' => __('Rendering Performance Score', 'wpshadow'), 'description' => __('Analyzes paint times, style/layout calculations. Poor rendering = UX issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
