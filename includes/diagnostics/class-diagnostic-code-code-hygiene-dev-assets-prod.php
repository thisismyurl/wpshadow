<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Dev Assets in Production
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-dev-assets-prod
 * Training: https://wpshadow.com/training/code-hygiene-dev-assets-prod
 */
class Diagnostic_Code_CODE_HYGIENE_DEV_ASSETS_PROD {
    public static function check() {
        return [
            'id' => 'code-hygiene-dev-assets-prod',
            'title' => __('Dev Assets in Production', 'wpshadow'),
            'description' => __('Flags source maps, test files shipped to production.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-dev-assets-prod',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-dev-assets-prod',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

