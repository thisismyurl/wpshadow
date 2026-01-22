<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Social-proof-presence {
  public static function check() {
    return ['id' => 'monitor-social_proof_presence', 'title' => __('Social Proof Elements Check', 'wpshadow'), 'description' => __('Verifies reviews, testimonials, user counts. Missing = trust signal gap.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
