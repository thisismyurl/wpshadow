<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Review-schema-usage {
  public static function check() {
    return ['id' => 'monitor-review_schema_usage', 'title' => __('Review Schema Usage Rate', 'wpshadow'), 'description' => __('Tracks % of reviewable content with review schema. Low usage = missing CTR boost.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
