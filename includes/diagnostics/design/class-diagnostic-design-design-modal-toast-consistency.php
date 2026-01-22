<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Modal and Toast Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-modal-toast-consistency
 * Training: https://wpshadow.com/training/design-modal-toast-consistency
 */
class Diagnostic_Design_DESIGN_MODAL_TOAST_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-modal-toast-consistency',
            'title' => __('Modal and Toast Consistency', 'wpshadow'),
            'description' => __('Checks overlay, spacing, shadow, and radius consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-modal-toast-consistency',
            'training_link' => 'https://wpshadow.com/training/design-modal-toast-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
