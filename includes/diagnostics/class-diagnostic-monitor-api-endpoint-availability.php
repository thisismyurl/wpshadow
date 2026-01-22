<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_API_Endpoint_Availability {
    public static function check() {
        return ['id' => 'monitor-api-availability', 'title' => __('API Endpoint Availability', 'wpshadow'), 'description' => __('Monitors REST API endpoints. Broken API = broken headless apps, integrations fail silently, user data lost.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/api-health/', 'training_link' => 'https://wpshadow.com/training/rest-api/', 'auto_fixable' => false, 'threat_level' => 9];
    }
}
