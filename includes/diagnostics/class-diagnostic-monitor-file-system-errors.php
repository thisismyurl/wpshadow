<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_File_System_Errors {
    public static function check() {
        return ['id' => 'monitor-fs-errors', 'title' => __('File System I/O Errors', 'wpshadow'), 'description' => __('Detects file read/write errors, permission issues, inode exhaustion. Prevents silent data loss and backup failures.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/file-system-health/', 'training_link' => 'https://wpshadow.com/training/storage-integrity/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
