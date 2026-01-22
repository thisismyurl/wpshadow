<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Hero Section Image Quality
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hero-image-optimization
 * Training: https://wpshadow.com/training/design-hero-image-optimization
 */
class Diagnostic_Design_HERO_IMAGE_OPTIMIZATION {
    public static function check() {
        return [
            'id' => 'design-hero-image-optimization',
            'title' => __('Hero Section Image Quality', 'wpshadow'),
            'description' => __('Validates hero image resolution, aspect ratio, mobile vs desktop variants, WebP fallback.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hero-image-optimization',
            'training_link' => 'https://wpshadow.com/training/design-hero-image-optimization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
