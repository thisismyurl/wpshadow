<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Header-tag-structure {
  public static function check() {
    return ['id' => 'monitor-header_tag_structure', 'title' => __('Header Tag (H1-H6) Structure', 'wpshadow'), 'description' => __('Verifies logical hierarchy. Missing H1 or broken hierarchy = poor UX signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
