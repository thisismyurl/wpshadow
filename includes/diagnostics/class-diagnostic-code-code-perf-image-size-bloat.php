<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Excessive Image Sizes
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-image-size-bloat
 * Training: https://wpshadow.com/training/code-perf-image-size-bloat
 */
class Diagnostic_Code_CODE_PERF_IMAGE_SIZE_BLOAT {
    public static function check() {
        return [
            'id' => 'code-perf-image-size-bloat',
            'title' => __('Excessive Image Sizes', 'wpshadow'),
            'description' => __('Flags over-registration of image sizes or unused variations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-image-size-bloat',
            'training_link' => 'https://wpshadow.com/training/code-perf-image-size-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

