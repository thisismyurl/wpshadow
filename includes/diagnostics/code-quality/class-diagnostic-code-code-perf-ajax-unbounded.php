<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: admin-ajax Unbounded
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-ajax-unbounded
 * Training: https://wpshadow.com/training/code-perf-ajax-unbounded
 */
class Diagnostic_Code_CODE_PERF_AJAX_UNBOUNDED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-ajax-unbounded',
            'title' => __('admin-ajax Unbounded', 'wpshadow'),
            'description' => __('Flags AJAX handlers without input validation or rate limiting.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-ajax-unbounded',
            'training_link' => 'https://wpshadow.com/training/code-perf-ajax-unbounded',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}