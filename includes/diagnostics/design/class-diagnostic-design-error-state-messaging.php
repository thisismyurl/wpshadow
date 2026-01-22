<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Error State Messaging Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-error-state-messaging
 * Training: https://wpshadow.com/training/design-error-state-messaging
 */
class Diagnostic_Design_ERROR_STATE_MESSAGING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-error-state-messaging',
            'title' => __('Error State Messaging Design', 'wpshadow'),
            'description' => __('Verifies error messages clear, constructive, suggest solutions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-error-state-messaging',
            'training_link' => 'https://wpshadow.com/training/design-error-state-messaging',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
