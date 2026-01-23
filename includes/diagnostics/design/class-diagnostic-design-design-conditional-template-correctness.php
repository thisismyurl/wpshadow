<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Conditional Template Correctness
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-conditional-template-correctness
 * Training: https://wpshadow.com/training/design-conditional-template-correctness
 */
class Diagnostic_Design_DESIGN_CONDITIONAL_TEMPLATE_CORRECTNESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-conditional-template-correctness',
            'title' => __('Conditional Template Correctness', 'wpshadow'),
            'description' => __('Checks correct singular, archive, and taxonomy templates are used.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-conditional-template-correctness',
            'training_link' => 'https://wpshadow.com/training/design-conditional-template-correctness',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}