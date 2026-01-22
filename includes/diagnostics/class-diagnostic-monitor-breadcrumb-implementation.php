<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Breadcrumb-implementation {
  public static function check() {
    return ['id' => 'monitor-breadcrumb_implementation', 'title' => __('Breadcrumb Schema Validation', 'wpshadow'), 'description' => __('Verifies breadcrumb markup. Missing = lost SERP feature opportunity.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
