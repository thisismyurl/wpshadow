<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Backup_Size_Anomaly.php {
    public static function check() {
        return ['id' => 'monitor-backup-size', 'title' => __('Backup Size Anomaly Detection', 'wpshadow'), 'description' => __('Detects abnormal backup size growth. Indicates database bloat, media bloat, or hack injecting files.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/backup-maintenance/', 'training_link' => 'https://wpshadow.com/training/storage-optimization/', 'auto_fixable' => false, 'threat_level' => 5];
    }
}
