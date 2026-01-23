<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Semantic extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-semantic_keyword_usage', 'title' => __('Semantic Keyword Usage', 'wpshadow'), 'description' => __('Monitors synonyms, related terms usage. Low diversity = missed topic depth.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}