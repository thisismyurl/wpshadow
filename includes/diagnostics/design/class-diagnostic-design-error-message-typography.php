<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Error Message Typography
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-error-message-typography
 * Training: https://wpshadow.com/training/design-error-message-typography
 */
class Diagnostic_Design_ERROR_MESSAGE_TYPOGRAPHY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-error-message-typography',
            'title' => __('Error Message Typography', 'wpshadow'),
            'description' => __('Verifies error text clear, size adequate.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-error-message-typography',
            'training_link' => 'https://wpshadow.com/training/design-error-message-typography',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
