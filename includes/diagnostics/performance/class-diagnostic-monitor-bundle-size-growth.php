<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Bundle extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-bundle_size_growth', 'title' => __('JavaScript Bundle Size Trend', 'wpshadow'), 'description' => __('Tracks JS size growth. Growth unchecked = page bloat.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
