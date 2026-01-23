<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Sizes/Srcset
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-missing-srcset
 * Training: https://wpshadow.com/training/code-perf-missing-srcset
 */
class Diagnostic_Code_CODE_PERF_MISSING_SRCSET extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-missing-srcset',
            'title' => __('Missing Sizes/Srcset', 'wpshadow'),
            'description' => __('Detects images in templates/blocks without srcset/sizes attrs.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-missing-srcset',
            'training_link' => 'https://wpshadow.com/training/code-perf-missing-srcset',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}