<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Semantic-keyword-usage {
  public static function check() {
    return ['id' => 'monitor-semantic_keyword_usage', 'title' => __('Semantic Keyword Usage', 'wpshadow'), 'description' => __('Monitors synonyms, related terms usage. Low diversity = missed topic depth.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
