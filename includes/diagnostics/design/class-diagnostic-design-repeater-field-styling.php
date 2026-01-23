<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Repeater Field Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-repeater-field-styling
 * Training: https://wpshadow.com/training/design-repeater-field-styling
 */
class Diagnostic_Design_REPEATER_FIELD_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-repeater-field-styling',
            'title' => __('Repeater Field Styling', 'wpshadow'),
            'description' => __('Checks ACF repeater fields styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-repeater-field-styling',
            'training_link' => 'https://wpshadow.com/training/design-repeater-field-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}