<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_First-contentful-paint {
  public static function check() {
    return ['id' => 'monitor-first_contentful_paint', 'title' => __('First Contentful Paint Trend', 'wpshadow'), 'description' => __('Monitors FCP degradation. Poor FCP = poor user signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
