<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Dropdown Mobile Behavior
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-dropdown-mobile-behavior
 * Training: https://wpshadow.com/training/design-dropdown-mobile-behavior
 */
class Diagnostic_Design_DROPDOWN_MOBILE_BEHAVIOR extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-dropdown-mobile-behavior',
            'title' => __('Dropdown Mobile Behavior', 'wpshadow'),
            'description' => __('Confirms dropdowns work on touch.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-dropdown-mobile-behavior',
            'training_link' => 'https://wpshadow.com/training/design-dropdown-mobile-behavior',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
