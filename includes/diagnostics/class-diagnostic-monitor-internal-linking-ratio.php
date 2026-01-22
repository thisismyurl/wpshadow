<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Internal-linking-ratio {
  public static function check() {
    return ['id' => 'monitor-internal_linking_ratio', 'title' => __('Internal Linking Ratio', 'wpshadow'), 'description' => __('Tracks contextual internal links per article. Low ratio = poor link juice distribution.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
