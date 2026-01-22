<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Video-description-quality {
  public static function check() {
    return ['id' => 'monitor-video_description_quality', 'title' => __('Video Description Quality', 'wpshadow'), 'description' => __('Checks video descriptions comprehensive. Thin descriptions = poor discoverability.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
