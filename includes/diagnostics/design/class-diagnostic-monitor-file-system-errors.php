<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_File_System_Errors extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-fs-errors', 'title' => __('File System I/O Errors', 'wpshadow'), 'description' => __('Detects file read/write errors, permission issues, inode exhaustion. Prevents silent data loss and backup failures.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/file-system-health/', 'training_link' => 'https://wpshadow.com/training/storage-integrity/', 'auto_fixable' => false, 'threat_level' => 8];
    }

}