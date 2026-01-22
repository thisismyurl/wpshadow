<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Image Bloat
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-image-bloat
 * Training: https://wpshadow.com/training/design-image-bloat
 */
class Diagnostic_Design_DESIGN_IMAGE_BLOAT {
    public static function check() {
        return [
            'id' => 'design-image-bloat',
            'title' => __('Image Bloat', 'wpshadow'),
            'description' => __('Flags oversized hero or background images lacking compression.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-image-bloat',
            'training_link' => 'https://wpshadow.com/training/design-image-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

