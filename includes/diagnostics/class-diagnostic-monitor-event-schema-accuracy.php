<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Event-schema-accuracy {
  public static function check() {
    return ['id' => 'monitor-event_schema_accuracy', 'title' => __('Event Schema Accuracy', 'wpshadow'), 'description' => __('Checks event dates, times, locations. Inaccurate = wrong info in search results.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
