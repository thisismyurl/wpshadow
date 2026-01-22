<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Breadcrumb extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-breadcrumb_implementation', 'title' => __('Breadcrumb Schema Validation', 'wpshadow'), 'description' => __('Verifies breadcrumb markup. Missing = lost SERP feature opportunity.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
