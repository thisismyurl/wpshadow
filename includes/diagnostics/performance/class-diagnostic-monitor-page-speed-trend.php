<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Page extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-page_speed_trend', 'title' => __('Page Speed Trend Analysis', 'wpshadow'), 'description' => __('Monitors speed changes over time. Degradation = ranking penalty incoming.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}