<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Depth extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-depth_analysis', 'title' => __('Content Depth Score', 'wpshadow'), 'description' => __('Analyzes average word count, structure. Shallow = low authority signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}