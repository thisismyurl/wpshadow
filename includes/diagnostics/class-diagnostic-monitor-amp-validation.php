<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Amp-validation {
  public static function check() {
    return ['id' => 'monitor-amp_validation', 'title' => __('AMP Page Validation', 'wpshadow'), 'description' => __('Verifies AMP pages valid. Invalid = no AMP badge in search.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
