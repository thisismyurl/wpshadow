<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: REST Lacking Pagination
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-rest-no-pagination
 * Training: https://wpshadow.com/training/code-rest-no-pagination
 */
class Diagnostic_Code_CODE_REST_NO_PAGINATION {
    public static function check() {
        return [
            'id' => 'code-rest-no-pagination',
            'title' => __('REST Lacking Pagination', 'wpshadow'),
            'description' => __('Detects REST endpoints returning all results without limit.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-rest-no-pagination',
            'training_link' => 'https://wpshadow.com/training/code-rest-no-pagination',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

