<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Error Recovery Simplicity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-error-recovery-simplicity
 * Training: https://wpshadow.com/training/design-error-recovery-simplicity
 */
class Diagnostic_Design_ERROR_RECOVERY_SIMPLICITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-error-recovery-simplicity',
            'title' => __('Error Recovery Simplicity', 'wpshadow'),
            'description' => __('Checks error recovery doesn't require re-entering.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-error-recovery-simplicity',
            'training_link' => 'https://wpshadow.com/training/design-error-recovery-simplicity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
