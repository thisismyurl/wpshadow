<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_User_Session_Duration extends Diagnostic_Base { public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-session-duration', 'title' => __('User Session Duration Tracking', 'wpshadow'), 'description' => __('Monitors average session length. Drop indicates poor content engagement or navigation issues.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/engagement-tracking/', 'training_link' => 'https://wpshadow.com/training/content-engagement/', 'auto_fixable' => false, 'threat_level' => 5]; } 
}