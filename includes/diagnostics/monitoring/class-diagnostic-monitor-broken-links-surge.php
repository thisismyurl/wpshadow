<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Broken_Links_Surge extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-broken-links', 'title' => __('Broken Links Surge Detection', 'wpshadow'), 'description' => __('Detects sudden increase in broken internal/external links. Indicates plugin/theme conflict, hack, or external dependency failure.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/link-health/', 'training_link' => 'https://wpshadow.com/training/link-maintenance/', 'auto_fixable' => false, 'threat_level' => 5];
    }
}
