<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Time extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-time_to_first_byte', 'title' => __('Time to First Byte Monitoring', 'wpshadow'), 'description' => __('Tracks TTFB trends. Increasing TTFB = infrastructure issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
