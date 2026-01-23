<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Consistency Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-form-consistency-score
 * Training: https://wpshadow.com/training/design-form-consistency-score
 */
class Diagnostic_Design_DESIGN_FORM_CONSISTENCY_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-consistency-score',
            'title' => __('Form Consistency Score', 'wpshadow'),
            'description' => __('Scores form control styling and error consistency per template.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-consistency-score',
            'training_link' => 'https://wpshadow.com/training/design-form-consistency-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}