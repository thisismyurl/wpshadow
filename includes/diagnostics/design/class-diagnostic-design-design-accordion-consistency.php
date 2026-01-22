<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Accordion Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-accordion-consistency
 * Training: https://wpshadow.com/training/design-accordion-consistency
 */
class Diagnostic_Design_DESIGN_ACCORDION_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-accordion-consistency',
            'title' => __('Accordion Consistency', 'wpshadow'),
            'description' => __('Checks chevron icons, spacing, and state consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-accordion-consistency',
            'training_link' => 'https://wpshadow.com/training/design-accordion-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
