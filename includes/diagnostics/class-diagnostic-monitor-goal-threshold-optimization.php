<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Goal-threshold-optimization {
  public static function check() {
    return ['id' => 'monitor-goal_threshold_optimization', 'title' => __('Goal Threshold Optimization', 'wpshadow'), 'description' => __('Analyzes if goal values realistic. Misaligned = false ROI calculation.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
