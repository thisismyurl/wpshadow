<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Cumulative extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-cumulative_layout_shift_trend', 'title' => __('CLS Trend Analysis', 'wpshadow'), 'description' => __('Monitors CLS stability. Increasing CLS = page quality signal loss.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}