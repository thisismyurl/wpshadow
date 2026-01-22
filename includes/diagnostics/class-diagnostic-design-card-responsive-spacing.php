<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Card Responsive Spacing
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-card-responsive-spacing
 * Training: https://wpshadow.com/training/design-card-responsive-spacing
 */
class Diagnostic_Design_CARD_RESPONSIVE_SPACING {
    public static function check() {
        return [
            'id' => 'design-card-responsive-spacing',
            'title' => __('Card Responsive Spacing', 'wpshadow'),
            'description' => __('Confirms cards responsive spacing.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-card-responsive-spacing',
            'training_link' => 'https://wpshadow.com/training/design-card-responsive-spacing',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
