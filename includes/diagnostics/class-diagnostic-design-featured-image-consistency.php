<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Featured Image Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-featured-image-consistency
 * Training: https://wpshadow.com/training/design-featured-image-consistency
 */
class Diagnostic_Design_FEATURED_IMAGE_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-featured-image-consistency',
            'title' => __('Featured Image Consistency', 'wpshadow'),
            'description' => __('Verifies featured images properly sized, displayed consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-featured-image-consistency',
            'training_link' => 'https://wpshadow.com/training/design-featured-image-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
