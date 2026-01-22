<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Empty State Templates
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-empty-state-templates
 * Training: https://wpshadow.com/training/design-empty-state-templates
 */
class Diagnostic_Design_DESIGN_EMPTY_STATE_TEMPLATES {
    public static function check() {
        return [
            'id' => 'design-empty-state-templates',
            'title' => __('Empty State Templates', 'wpshadow'),
            'description' => __('Checks empty archives and search show styled empty states.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-empty-state-templates',
            'training_link' => 'https://wpshadow.com/training/design-empty-state-templates',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

