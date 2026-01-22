<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Color Not Sole Differentiator
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-color-not-sole-differentiator
 * Training: https://wpshadow.com/training/design-color-not-sole-differentiator
 */
class Diagnostic_Design_COLOR_NOT_SOLE_DIFFERENTIATOR extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-color-not-sole-differentiator',
            'title' => __('Color Not Sole Differentiator', 'wpshadow'),
            'description' => __('Checks information conveyed with color also uses icons.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-color-not-sole-differentiator',
            'training_link' => 'https://wpshadow.com/training/design-color-not-sole-differentiator',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
