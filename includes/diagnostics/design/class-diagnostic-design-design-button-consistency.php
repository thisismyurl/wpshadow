<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-button-consistency
 * Training: https://wpshadow.com/training/design-button-consistency
 */
class Diagnostic_Design_DESIGN_BUTTON_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-consistency',
            'title' => __('Button Consistency', 'wpshadow'),
            'description' => __('Checks button variants for consistency across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-consistency',
            'training_link' => 'https://wpshadow.com/training/design-button-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}