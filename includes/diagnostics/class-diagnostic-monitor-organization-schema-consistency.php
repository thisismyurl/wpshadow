<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Organization-schema-consistency {
  public static function check() {
    return ['id' => 'monitor-organization_schema_consistency', 'title' => __('Organization Schema Consistency', 'wpshadow'), 'description' => __('Verifies company info uniform across pages. Inconsistent = trust signal lost.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
