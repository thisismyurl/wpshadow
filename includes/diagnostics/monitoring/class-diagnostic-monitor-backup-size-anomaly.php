<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Backup_Size_Anomaly extends Diagnostic_Base .php {
    public static function check(): ?array {
        return ['id' => 'monitor-backup-size', 'title' => __('Backup Size Anomaly Detection', 'wpshadow'), 'description' => __('Detects abnormal backup size growth. Indicates database bloat, media bloat, or hack injecting files.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/backup-maintenance/', 'training_link' => 'https://wpshadow.com/training/storage-optimization/', 'auto_fixable' => false, 'threat_level' => 5];
    }
}
