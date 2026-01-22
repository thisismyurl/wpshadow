<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Css-file-optimization {
  public static function check() {
    return ['id' => 'monitor-css_file_optimization', 'title' => __('CSS File Optimization', 'wpshadow'), 'description' => __('Verifies CSS minified, compressed. Unoptimized = slower rendering.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
