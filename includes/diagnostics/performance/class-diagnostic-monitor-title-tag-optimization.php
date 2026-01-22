<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Title extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-title_tag_optimization', 'title' => __('Title Tag Optimization Score', 'wpshadow'), 'description' => __('Checks title length, keyword position, clarity. Poor titles = low CTR.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
