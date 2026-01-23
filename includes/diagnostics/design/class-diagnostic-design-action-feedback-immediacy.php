<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Action Feedback Immediacy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-action-feedback-immediacy
 * Training: https://wpshadow.com/training/design-action-feedback-immediacy
 */
class Diagnostic_Design_ACTION_FEEDBACK_IMMEDIACY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-action-feedback-immediacy',
            'title' => __('Action Feedback Immediacy', 'wpshadow'),
            'description' => __('Confirms actions have immediate visual feedback.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-action-feedback-immediacy',
            'training_link' => 'https://wpshadow.com/training/design-action-feedback-immediacy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}