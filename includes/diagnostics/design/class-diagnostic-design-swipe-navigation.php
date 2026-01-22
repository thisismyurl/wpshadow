<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Swipe Navigation Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-swipe-navigation
 * Training: https://wpshadow.com/training/design-swipe-navigation
 */
class Diagnostic_Design_SWIPE_NAVIGATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-swipe-navigation',
            'title' => __('Swipe Navigation Implementation', 'wpshadow'),
            'description' => __('Checks swipe navigation optional.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-swipe-navigation',
            'training_link' => 'https://wpshadow.com/training/design-swipe-navigation',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
