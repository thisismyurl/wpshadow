<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Backup_Failure_Alert {
    public static function check() {
        return ['id' => 'monitor-backup-failure', 'title' => __('Backup Failure Alert', 'wpshadow'), 'description' => __('Detects when scheduled backups fail. No backup = no recovery if disaster strikes. Critical for business continuity.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/backup-strategy/', 'training_link' => 'https://wpshadow.com/training/disaster-recovery/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
