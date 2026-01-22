<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Rich-snippet-eligibility {
  public static function check() {
    return ['id' => 'monitor-rich_snippet_eligibility', 'title' => __('Rich Snippet Eligibility Check', 'wpshadow'), 'description' => __('Verifies content qualifies for rich snippets. Unqualified = lower SERP real estate.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
