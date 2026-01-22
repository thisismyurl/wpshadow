<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Bundle-size-growth {
  public static function check() {
    return ['id' => 'monitor-bundle_size_growth', 'title' => __('JavaScript Bundle Size Trend', 'wpshadow'), 'description' => __('Tracks JS size growth. Growth unchecked = page bloat.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
