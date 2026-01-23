<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hardcoded Paths/URLs
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-hardcoded-paths
 * Training: https://wpshadow.com/training/code-hygiene-hardcoded-paths
 */
class Diagnostic_Code_CODE_HYGIENE_HARDCODED_PATHS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-hardcoded-paths',
            'title' => __('Hardcoded Paths/URLs', 'wpshadow'),
            'description' => __('Detects WP_CONTENT_DIR hardcoded instead of WP APIs.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-hardcoded-paths',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-hardcoded-paths',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}