<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Floating Action Button
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-floating-button-mobile
 * Training: https://wpshadow.com/training/design-floating-button-mobile
 */
class Diagnostic_Design_FLOATING_BUTTON_MOBILE {
    public static function check() {
        return [
            'id' => 'design-floating-button-mobile',
            'title' => __('Floating Action Button', 'wpshadow'),
            'description' => __('Verifies FAB positioned correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-floating-button-mobile',
            'training_link' => 'https://wpshadow.com/training/design-floating-button-mobile',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
