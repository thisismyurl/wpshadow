<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Hero Section Height Responsiveness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hero-section-height-mobile
 * Training: https://wpshadow.com/training/design-hero-section-height-mobile
 */
class Diagnostic_Design_HERO_SECTION_HEIGHT_MOBILE {
    public static function check() {
        return [
            'id' => 'design-hero-section-height-mobile',
            'title' => __('Hero Section Height Responsiveness', 'wpshadow'),
            'description' => __('Confirms hero height adjusted for mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hero-section-height-mobile',
            'training_link' => 'https://wpshadow.com/training/design-hero-section-height-mobile',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
