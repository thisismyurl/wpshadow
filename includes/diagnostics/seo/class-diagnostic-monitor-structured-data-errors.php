<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Structured extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-structured_data_errors', 'title' => __('Structured Data Error Rate', 'wpshadow'), 'description' => __('Tracks schema validation errors. Errors = features lost, knowledge panel disabled.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
