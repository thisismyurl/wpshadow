<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Cron_Job_Failures extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-cron-failures', 'description' => __('Tracks failed WordPress cron executions. Failed crons = missed scheduled tasks (backups, email, publishing, cleanup).', 'wpshadow'), 'title' => __('Cron Job Execution Failures', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/cron-health/', 'training_link' => 'https://wpshadow.com/training/scheduled-tasks/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
