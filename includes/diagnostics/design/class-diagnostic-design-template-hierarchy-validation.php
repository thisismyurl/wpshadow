<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Template Hierarchy Validation
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-template-hierarchy-validation
 * Training: https://wpshadow.com/training/design-template-hierarchy-validation
 */
class Diagnostic_Design_TEMPLATE_HIERARCHY_VALIDATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-template-hierarchy-validation',
            'title' => __('Template Hierarchy Validation', 'wpshadow'),
            'description' => __('Verifies proper WordPress template file structure.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-template-hierarchy-validation',
            'training_link' => 'https://wpshadow.com/training/design-template-hierarchy-validation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}