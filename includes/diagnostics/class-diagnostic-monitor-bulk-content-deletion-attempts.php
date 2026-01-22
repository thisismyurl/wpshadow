<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Bulk_Content_Deletion_Attempts {
    public static function check() {
        return ['id' => 'monitor-bulk-deletion', 'title' => __('Bulk Content Deletion Attempts', 'wpshadow'), 'description' => __('Detects mass deletion of posts/pages. Hack indicator or accidental bulk operation that needs recovery.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/deletion-protection/', 'training_link' => 'https://wpshadow.com/training/disaster-recovery/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
