<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_External-linking-quality {
  public static function check() {
    return ['id' => 'monitor-external_linking_quality', 'title' => __('External Link Quality Score', 'wpshadow'), 'description' => __('Analyzes quality of outbound links. Low quality = reduced credibility.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
