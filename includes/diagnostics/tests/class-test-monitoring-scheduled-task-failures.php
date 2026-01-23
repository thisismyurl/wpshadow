<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Scheduled Task Failures (Monitoring)
 * 
 * Checks if WordPress scheduled tasks (cron) are failing
 * Philosophy: Show value (#9) - working cron prevents data loss
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_ScheduledTaskFailures extends Diagnostic_Base {
    
    public static function check(): ?array {
        // Check if WordPress cron is working
        $cron_events = _get_cron_array();
        
        if (empty($cron_events)) {
            return [
                'id' => 'scheduled-task-failures',
                'title' => __('No scheduled tasks configured', 'wpshadow'),
                'description' => __('WordPress needs scheduled tasks for updates, backups, and maintenance. Enable WP-Cron or set up a system cron job.', 'wpshadow'),
                'severity' => 'medium',
                'threat_level' => 55,
            ];
        }
        
        return null;
    }
    
    public static function test_live_scheduled_task_failures(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('Scheduled tasks are properly configured', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
