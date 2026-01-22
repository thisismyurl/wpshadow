<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Case-study-availability {
  public static function check() {
    return ['id' => 'monitor-case_study_availability', 'title' => __('Case Study Availability Check', 'wpshadow'), 'description' => __('Tracks case studies on site. Missing = lower conversion for B2B.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
