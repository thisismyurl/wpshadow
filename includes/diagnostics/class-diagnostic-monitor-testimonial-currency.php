<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Testimonial-currency {
  public static function check() {
    return ['id' => 'monitor-testimonial_currency', 'title' => __('Testimonial Freshness', 'wpshadow'), 'description' => __('Checks testimonial dates. Old testimonials = credibility loss.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
