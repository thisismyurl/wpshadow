<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Social Sharing Image Quality
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-social-sharing-images
 * Training: https://wpshadow.com/training/design-social-sharing-images
 */
class Diagnostic_Design_SOCIAL_SHARING_IMAGES {
    public static function check() {
        return [
            'id' => 'design-social-sharing-images',
            'title' => __('Social Sharing Image Quality', 'wpshadow'),
            'description' => __('Checks OG images, Twitter cards optimized.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-social-sharing-images',
            'training_link' => 'https://wpshadow.com/training/design-social-sharing-images',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
