<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Largest extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-largest_contentful_paint_trend', 'title' => __('LCP Trend Analysis', 'wpshadow'), 'description' => __('Tracks LCP over time. Degrading LCP = ranking risk.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
