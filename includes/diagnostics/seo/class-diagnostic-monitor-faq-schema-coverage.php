<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Faq extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-faq_schema_coverage', 'title' => __('FAQ Schema Coverage', 'wpshadow'), 'description' => __('Identifies pages ideal for FAQ schema. Missing = left money on table.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
