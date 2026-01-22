<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Image Size Over-Registration
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-image-sizes-bloat
 * Training: https://wpshadow.com/training/code-memory-image-sizes-bloat
 */
class Diagnostic_Code_CODE_MEMORY_IMAGE_SIZES_BLOAT {
    public static function check() {
        return [
            'id' => 'code-memory-image-sizes-bloat',
            'title' => __('Image Size Over-Registration', 'wpshadow'),
            'description' => __('Flags excessive image sizes or unused crop variations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-image-sizes-bloat',
            'training_link' => 'https://wpshadow.com/training/code-memory-image-sizes-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

