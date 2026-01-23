<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_External extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-external_linking_quality', 'title' => __('External Link Quality Score', 'wpshadow'), 'description' => __('Analyzes quality of outbound links. Low quality = reduced credibility.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}