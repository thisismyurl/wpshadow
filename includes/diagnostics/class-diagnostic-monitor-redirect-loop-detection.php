<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Redirect_Loop_Detection {
    public static function check() {
        return ['id' => 'monitor-redirect-loops', 'title' => __('Redirect Loop Detection', 'wpshadow'), 'description' => __('Detects A→B→A redirect chains. Breaks crawling, user navigation, ranking. Indicates misconfigured redirects.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/redirect-health/', 'training_link' => 'https://wpshadow.com/training/redirect-management/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
