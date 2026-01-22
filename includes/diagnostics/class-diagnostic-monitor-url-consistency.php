<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Url-consistency {
  public static function check() {
    return ['id' => 'monitor-url_consistency', 'title' => __('URL Consistency Checker', 'wpshadow'), 'description' => __('Detects non-canonical URLs, protocol mismatches (http/https), www/non-www variations.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
