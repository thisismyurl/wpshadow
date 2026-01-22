<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Referrer_Spam_Detection extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-referrer-spam', 'title' => __('Referrer Spam Detection', 'wpshadow'), 'description' => __('Identifies spam referrers with malicious links. Logs without counting as valid traffic; cleans analytics data.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/analytics-spam/', 'training_link' => 'https://wpshadow.com/training/traffic-quality/', 'auto_fixable' => false, 'threat_level' => 3];
    }
}
