<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Largest-contentful-paint-trend {
  public static function check() {
    return ['id' => 'monitor-largest_contentful_paint_trend', 'title' => __('LCP Trend Analysis', 'wpshadow'), 'description' => __('Tracks LCP over time. Degrading LCP = ranking risk.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
