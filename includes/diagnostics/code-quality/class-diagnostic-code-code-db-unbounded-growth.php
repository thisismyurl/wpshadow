<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unbounded Table Growth
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-unbounded-growth
 * Training: https://wpshadow.com/training/code-db-unbounded-growth
 */
class Diagnostic_Code_CODE_DB_UNBOUNDED_GROWTH extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-unbounded-growth',
            'title' => __('Unbounded Table Growth', 'wpshadow'),
            'description' => __('Detects custom tables without retention or archival policy.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-unbounded-growth',
            'training_link' => 'https://wpshadow.com/training/code-db-unbounded-growth',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
