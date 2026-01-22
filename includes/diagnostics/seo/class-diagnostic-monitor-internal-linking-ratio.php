<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Internal extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-internal_linking_ratio', 'title' => __('Internal Linking Ratio', 'wpshadow'), 'description' => __('Tracks contextual internal links per article. Low ratio = poor link juice distribution.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
