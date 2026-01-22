<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Indexation_Drop extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-index-drop', 'title' => __('Google Indexation Drop', 'wpshadow'), 'description' => __('Detects sudden decrease in indexed pages. Indicates noindex bug, crawl block, or penalty. Immediate investigation needed.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/indexation-health/', 'training_link' => 'https://wpshadow.com/training/search-console-monitoring/', 'auto_fixable' => false, 'threat_level' => 10];
    }
}
