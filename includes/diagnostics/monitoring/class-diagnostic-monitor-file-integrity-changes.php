<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_File_Integrity_Changes extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-file-integrity', 'title' => __('Core File Integrity Changes', 'wpshadow'), 'description' => __('Detects when WordPress core, theme, plugin files are modified. Indicates hack attempt or unauthorized changes.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/file-integrity/', 'training_link' => 'https://wpshadow.com/training/hack-recovery/', 'auto_fixable' => false, 'threat_level' => 10];
    }

}