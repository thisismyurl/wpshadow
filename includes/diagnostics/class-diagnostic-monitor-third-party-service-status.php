<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Third_Party_Service_Status.php {
    public static function check() {
        return ['id' => 'monitor-service-status', 'title' => __('Third-Party Service Status Monitoring', 'wpshadow'), 'description' => __('Tracks status of external services (CDN, email, payment, API). Degradation = user-facing impact.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/service-dependencies/', 'training_link' => 'https://wpshadow.com/training/dependency-management/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
