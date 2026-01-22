<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Interaction-to-next-paint {
  public static function check() {
    return ['id' => 'monitor-interaction_to_next_paint', 'title' => __('Interaction to Next Paint (INP)', 'wpshadow'), 'description' => __('Tracks responsive interaction delays. High INP = UX penalty.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
