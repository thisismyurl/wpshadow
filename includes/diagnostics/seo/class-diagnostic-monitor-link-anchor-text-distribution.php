<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Link extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-link_anchor_text_distribution', 'title' => __('Anchor Text Distribution Analysis', 'wpshadow'), 'description' => __('Verifies anchor text variety. Identical anchors = keyword stuffing signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
