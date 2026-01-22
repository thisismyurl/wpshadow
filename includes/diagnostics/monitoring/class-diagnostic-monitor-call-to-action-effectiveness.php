<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Call extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-call_to_action_effectiveness', 'title' => __('Call-to-Action Effectiveness', 'wpshadow'), 'description' => __('Tracks CTA presence and variety. Missing CTAs = conversion loss.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
