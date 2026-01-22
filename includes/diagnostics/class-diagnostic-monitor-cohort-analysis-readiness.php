<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Cohort-analysis-readiness {
  public static function check() {
    return ['id' => 'monitor-cohort_analysis_readiness', 'title' => __('Cohort Analysis Readiness', 'wpshadow'), 'description' => __('Verifies cohort tracking enabled. Disabled = can't measure retention.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
