<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button Text Appropriateness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-text-appropriateness
 * Training: https://wpshadow.com/training/design-button-text-appropriateness
 */
class Diagnostic_Design_BUTTON_TEXT_APPROPRIATENESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-text-appropriateness',
            'title' => __('Button Text Appropriateness', 'wpshadow'),
            'description' => __('Checks button text action-oriented.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-text-appropriateness',
            'training_link' => 'https://wpshadow.com/training/design-button-text-appropriateness',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}