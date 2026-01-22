<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Interaction extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-interaction_to_next_paint', 'title' => __('Interaction to Next Paint (INP)', 'wpshadow'), 'description' => __('Tracks responsive interaction delays. High INP = UX penalty.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
