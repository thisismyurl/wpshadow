<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Suspicious_Upload_Patterns {
    public static function check() {
        return ['id' => 'monitor-upload-patterns', 'title' => __('Suspicious File Upload Patterns', 'wpshadow'), 'description' => __('Detects executable uploads, mass uploads, uploads to wrong directories. Blocks backdoor deployment vectors.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/upload-security/', 'training_link' => 'https://wpshadow.com/training/file-handling/', 'auto_fixable' => false, 'threat_level' => 9];
    }
}
