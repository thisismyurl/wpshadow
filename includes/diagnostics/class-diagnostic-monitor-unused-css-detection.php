<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Unused-css-detection {
  public static function check() {
    return ['id' => 'monitor-unused_css_detection', 'title' => __('Unused CSS Detection', 'wpshadow'), 'description' => __('Identifies unused CSS rules. Unused = wasted bandwidth.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
