<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Time-to-first-byte {
  public static function check() {
    return ['id' => 'monitor-time_to_first_byte', 'title' => __('Time to First Byte Monitoring', 'wpshadow'), 'description' => __('Tracks TTFB trends. Increasing TTFB = infrastructure issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
