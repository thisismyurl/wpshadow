<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Card Component Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-card-component-consistency
 * Training: https://wpshadow.com/training/design-card-component-consistency
 */
class Diagnostic_Design_CARD_COMPONENT_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-card-component-consistency',
            'title' => __('Card Component Consistency', 'wpshadow'),
            'description' => __('Checks all cards use same border-radius, shadow, padding, and hover behavior.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-card-component-consistency',
            'training_link' => 'https://wpshadow.com/training/design-card-component-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
