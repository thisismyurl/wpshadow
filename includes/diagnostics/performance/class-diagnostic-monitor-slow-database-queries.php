<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Slow_Database_Queries extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-slow-queries', 'title' => __('Slow Database Query Detection', 'wpshadow'), 'description' => __('Logs queries taking >500ms. Identifies unoptimized queries causing page slowdown. Actionable: show which plugin/theme caused it.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/query-optimization/', 'training_link' => 'https://wpshadow.com/training/database-indexing/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
