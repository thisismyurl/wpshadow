<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_API_Rate_Limit_Tracking extends Diagnostic_Base .php {
    public static function check(): ?array {
        return ['id' => 'monitor-api-limits', 'title' => __('External API Rate Limit Tracking', 'wpshadow'), 'description' => __('Monitors API calls against provider limits (Google, OpenAI, etc). Prevents exceeded limits that break integrations.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/api-management/', 'training_link' => 'https://wpshadow.com/training/third-party-services/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
