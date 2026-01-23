<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Flexbox Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-flexbox-implementation
 * Training: https://wpshadow.com/training/design-css-flexbox-implementation
 */
class Diagnostic_Design_CSS_FLEXBOX_IMPLEMENTATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-flexbox-implementation',
            'title' => __('Flexbox Implementation', 'wpshadow'),
            'description' => __('Confirms Flexbox used for linear layouts.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-flexbox-implementation',
            'training_link' => 'https://wpshadow.com/training/design-css-flexbox-implementation',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}