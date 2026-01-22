<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Video extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-video_schema_validation', 'title' => __('Video Schema Validation', 'wpshadow'), 'description' => __('Verifies video duration, thumbnails, descriptions. Invalid = videos don't appear in search.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
