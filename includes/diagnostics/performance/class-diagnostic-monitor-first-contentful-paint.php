<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_First extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-first_contentful_paint', 'title' => __('First Contentful Paint Trend', 'wpshadow'), 'description' => __('Monitors FCP degradation. Poor FCP = poor user signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
