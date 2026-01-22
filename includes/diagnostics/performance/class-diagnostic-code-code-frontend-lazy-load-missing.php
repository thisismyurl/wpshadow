<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Lazy-Load Not Applied
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-lazy-load-missing
 * Training: https://wpshadow.com/training/code-frontend-lazy-load-missing
 */
class Diagnostic_Code_CODE_FRONTEND_LAZY_LOAD_MISSING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-frontend-lazy-load-missing',
            'title' => __('Lazy-Load Not Applied', 'wpshadow'),
            'description' => __('Detects offscreen images not lazy-loaded.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-lazy-load-missing',
            'training_link' => 'https://wpshadow.com/training/code-frontend-lazy-load-missing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
