<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Gross-margin-quality {
  public static function check() {
    return ['id' => 'monitor-gross_margin_quality', 'title' => __('Gross Margin Quality Score', 'wpshadow'), 'description' => __('Analyzes product profitability. Declining = cost or pricing issue.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
