<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Video-title-optimization {
  public static function check() {
    return ['id' => 'monitor-video_title_optimization', 'title' => __('Video Title Optimization', 'wpshadow'), 'description' => __('Verifies video titles keyword-optimized. Poor titles = YouTube ranking loss.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
