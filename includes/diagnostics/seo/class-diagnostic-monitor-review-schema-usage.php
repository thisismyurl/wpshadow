<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Review extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-review_schema_usage', 'title' => __('Review Schema Usage Rate', 'wpshadow'), 'description' => __('Tracks % of reviewable content with review schema. Low usage = missing CTR boost.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}