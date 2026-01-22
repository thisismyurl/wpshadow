<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Video extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-video_description_quality', 'title' => __('Video Description Quality', 'wpshadow'), 'description' => __('Checks video descriptions comprehensive. Thin descriptions = poor discoverability.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
