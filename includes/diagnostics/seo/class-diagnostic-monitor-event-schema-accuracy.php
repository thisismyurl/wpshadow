<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Event extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-event_schema_accuracy', 'title' => __('Event Schema Accuracy', 'wpshadow'), 'description' => __('Checks event dates, times, locations. Inaccurate = wrong info in search results.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
