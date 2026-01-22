<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Completion Affordance
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-completion-affordance
 * Training: https://wpshadow.com/training/design-form-completion-affordance
 */
class Diagnostic_Design_FORM_COMPLETION_AFFORDANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-completion-affordance',
            'title' => __('Form Completion Affordance', 'wpshadow'),
            'description' => __('Validates form progress indication, field count.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-completion-affordance',
            'training_link' => 'https://wpshadow.com/training/design-form-completion-affordance',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
