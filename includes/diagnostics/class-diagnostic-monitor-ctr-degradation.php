<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_CTR_Degradation {
    public static function check() {
        return ['id' => 'monitor-ctr-degradation', 'title' => __('Click-Through Rate Degradation', 'wpshadow'), 'description' => __('Detects declining CTR in SERPs. Indicates title/meta description issues, competitors taking clicks, or ranking position loss.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ctr-optimization/', 'training_link' => 'https://wpshadow.com/training/serp-testing/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
