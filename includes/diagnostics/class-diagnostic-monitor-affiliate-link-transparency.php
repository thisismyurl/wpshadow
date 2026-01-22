<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Affiliate-link-transparency {
  public static function check() {
    return ['id' => 'monitor-affiliate_link_transparency', 'title' => __('Affiliate Link Transparency', 'wpshadow'), 'description' => __('Verifies affiliate links marked with disclosure. Unmarked = FTC violation.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
