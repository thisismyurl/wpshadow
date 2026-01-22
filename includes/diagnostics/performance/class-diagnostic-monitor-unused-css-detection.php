<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Unused extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-unused_css_detection', 'title' => __('Unused CSS Detection', 'wpshadow'), 'description' => __('Identifies unused CSS rules. Unused = wasted bandwidth.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
