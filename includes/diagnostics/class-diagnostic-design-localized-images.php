<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Localized Image Assets
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-localized-images
 * Training: https://wpshadow.com/training/design-localized-images
 */
class Diagnostic_Design_LOCALIZED_IMAGES {
    public static function check() {
        return [
            'id' => 'design-localized-images',
            'title' => __('Localized Image Assets', 'wpshadow'),
            'description' => __('Confirms language-specific images present.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-localized-images',
            'training_link' => 'https://wpshadow.com/training/design-localized-images',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
