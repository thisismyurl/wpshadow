<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Trust extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-trust_badge_visibility', 'title' => __('Trust Badge Visibility Check', 'wpshadow'), 'description' => __('Verifies security seals, certifications visible. Hidden = trust signal lost.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}