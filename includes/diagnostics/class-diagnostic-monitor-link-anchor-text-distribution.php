<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Link-anchor-text-distribution {
  public static function check() {
    return ['id' => 'monitor-link_anchor_text_distribution', 'title' => __('Anchor Text Distribution Analysis', 'wpshadow'), 'description' => __('Verifies anchor text variety. Identical anchors = keyword stuffing signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
