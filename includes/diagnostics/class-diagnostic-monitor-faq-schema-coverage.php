<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Faq-schema-coverage {
  public static function check() {
    return ['id' => 'monitor-faq_schema_coverage', 'title' => __('FAQ Schema Coverage', 'wpshadow'), 'description' => __('Identifies pages ideal for FAQ schema. Missing = left money on table.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
