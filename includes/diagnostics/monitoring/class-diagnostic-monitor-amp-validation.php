<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Amp extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-amp_validation', 'title' => __('AMP Page Validation', 'wpshadow'), 'description' => __('Verifies AMP pages valid. Invalid = no AMP badge in search.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}