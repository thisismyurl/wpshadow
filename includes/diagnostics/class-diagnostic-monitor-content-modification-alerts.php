<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Content_Modification_Alerts {
    public static function check() {
        return ['id' => 'monitor-content-changes', 'title' => __('Unauthorized Content Modifications', 'wpshadow'), 'description' => __('Detects when posts/pages are changed without expected authors. Indicates hack, defacement, or internal sabotage.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/content-integrity/', 'training_link' => 'https://wpshadow.com/training/content-security/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
