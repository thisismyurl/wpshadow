<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Text Transform CSS-Only
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-text-transform-css-only
 * Training: https://wpshadow.com/training/design-text-transform-css-only
 */
class Diagnostic_Design_TEXT_TRANSFORM_CSS_ONLY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-text-transform-css-only',
            'title' => __('Text Transform CSS-Only', 'wpshadow'),
            'description' => __('Validates text case controlled via CSS.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-text-transform-css-only',
            'training_link' => 'https://wpshadow.com/training/design-text-transform-css-only',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}