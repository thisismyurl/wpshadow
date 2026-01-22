<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Gallery Spacing
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-gallery-spacing
 * Training: https://wpshadow.com/training/design-block-gallery-spacing
 */
class Diagnostic_Design_DESIGN_BLOCK_GALLERY_SPACING {
    public static function check() {
        return [
            'id' => 'design-block-gallery-spacing',
            'title' => __('Block Gallery Spacing', 'wpshadow'),
            'description' => __('Ensures gallery gutters, ratios, and captions are consistent.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-gallery-spacing',
            'training_link' => 'https://wpshadow.com/training/design-block-gallery-spacing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

