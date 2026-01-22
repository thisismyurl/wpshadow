<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Success Feedback Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-success-feedback-design
 * Training: https://wpshadow.com/training/design-success-feedback-design
 */
class Diagnostic_Design_SUCCESS_FEEDBACK_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-success-feedback-design',
            'title' => __('Success Feedback Design', 'wpshadow'),
            'description' => __('Confirms success messages visible, use color + icon + text, persist.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-success-feedback-design',
            'training_link' => 'https://wpshadow.com/training/design-success-feedback-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
