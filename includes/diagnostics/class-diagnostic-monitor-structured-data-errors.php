<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Structured-data-errors {
  public static function check() {
    return ['id' => 'monitor-structured_data_errors', 'title' => __('Structured Data Error Rate', 'wpshadow'), 'description' => __('Tracks schema validation errors. Errors = features lost, knowledge panel disabled.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
