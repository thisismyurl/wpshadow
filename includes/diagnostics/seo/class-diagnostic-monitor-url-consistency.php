<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Url extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-url_consistency', 'title' => __('URL Consistency Checker', 'wpshadow'), 'description' => __('Detects non-canonical URLs, protocol mismatches (http/https), www/non-www variations.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
