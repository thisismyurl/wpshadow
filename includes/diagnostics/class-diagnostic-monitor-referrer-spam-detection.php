<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Referrer_Spam_Detection {
    public static function check() {
        return ['id' => 'monitor-referrer-spam', 'title' => __('Referrer Spam Detection', 'wpshadow'), 'description' => __('Identifies spam referrers with malicious links. Logs without counting as valid traffic; cleans analytics data.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/analytics-spam/', 'training_link' => 'https://wpshadow.com/training/traffic-quality/', 'auto_fixable' => false, 'threat_level' => 3];
    }
}
