<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button Hover Feedback
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-hover-feedback
 * Training: https://wpshadow.com/training/design-button-hover-feedback
 */
class Diagnostic_Design_BUTTON_HOVER_FEEDBACK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-hover-feedback',
            'title' => __('Button Hover Feedback', 'wpshadow'),
            'description' => __('Validates button hover shows feedback.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-hover-feedback',
            'training_link' => 'https://wpshadow.com/training/design-button-hover-feedback',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}