<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Preprocessor Organization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-preprocessor-organization
 * Training: https://wpshadow.com/training/design-css-preprocessor-organization
 */
class Diagnostic_Design_CSS_PREPROCESSOR_ORGANIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-preprocessor-organization',
            'title' => __('CSS Preprocessor Organization', 'wpshadow'),
            'description' => __('Validates Sass/LESS well-organized.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-preprocessor-organization',
            'training_link' => 'https://wpshadow.com/training/design-css-preprocessor-organization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
