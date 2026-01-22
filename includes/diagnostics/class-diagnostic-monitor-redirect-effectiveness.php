<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Redirect-effectiveness {
  public static function check() {
    return ['id' => 'monitor-redirect_effectiveness', 'title' => __('Redirect Effectiveness Score', 'wpshadow'), 'description' => __('Measures how many old URLs properly redirect. Orphaned URLs = lost link equity.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
