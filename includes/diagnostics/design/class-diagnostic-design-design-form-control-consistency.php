<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Control Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-form-control-consistency
 * Training: https://wpshadow.com/training/design-form-control-consistency
 */
class Diagnostic_Design_DESIGN_FORM_CONTROL_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-control-consistency',
            'title' => __('Form Control Consistency', 'wpshadow'),
            'description' => __('Checks inputs, selects, and checkboxes styling consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-control-consistency',
            'training_link' => 'https://wpshadow.com/training/design-form-control-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
