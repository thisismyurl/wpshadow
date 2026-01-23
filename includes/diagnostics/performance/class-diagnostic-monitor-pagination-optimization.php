<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Pagination extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-pagination_optimization', 'title' => __('Pagination Structure Validation', 'wpshadow'), 'description' => __('Verifies rel=next/prev, canonicals on paginated content. Broken pagination = indexation issues.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}