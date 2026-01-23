<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Json extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-json_ld_conflicts', 'title' => __('JSON-LD Conflict Detection', 'wpshadow'), 'description' => __('Detects conflicting schema markup. Conflicts = Google ignores schema.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}