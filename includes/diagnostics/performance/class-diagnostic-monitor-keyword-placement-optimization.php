<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Keyword extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-keyword_placement_optimization', 'title' => __('Keyword Placement Optimization', 'wpshadow'), 'description' => __('Verifies keywords in optimal positions (H1, first 100 words). Poor placement = weak relevance signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}