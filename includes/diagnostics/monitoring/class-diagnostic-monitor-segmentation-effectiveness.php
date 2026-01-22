<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Segmentation extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-segmentation_effectiveness', 'title' => __('Segmentation Effectiveness', 'wpshadow'), 'description' => __('Checks audience segments properly defined. Poor segmentation = unclear insights.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
