<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Input Placeholder Style
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-input-placeholder-style
 * Training: https://wpshadow.com/training/design-input-placeholder-style
 */
class Diagnostic_Design_INPUT_PLACEHOLDER_STYLE {
    public static function check() {
        return [
            'id' => 'design-input-placeholder-style',
            'title' => __('Input Placeholder Style', 'wpshadow'),
            'description' => __('Checks placeholder text styling.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-input-placeholder-style',
            'training_link' => 'https://wpshadow.com/training/design-input-placeholder-style',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
