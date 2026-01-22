<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Mobile-cta-optimization {
  public static function check() {
    return ['id' => 'monitor-mobile_cta_optimization', 'title' => __('Mobile CTA Optimization', 'wpshadow'), 'description' => __('Verifies CTAs mobile-friendly. Mobile CTAs hidden = traffic loss on mobile.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
