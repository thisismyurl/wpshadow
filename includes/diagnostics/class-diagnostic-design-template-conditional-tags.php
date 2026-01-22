<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Template Conditional Tags
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-template-conditional-tags
 * Training: https://wpshadow.com/training/design-template-conditional-tags
 */
class Diagnostic_Design_TEMPLATE_CONDITIONAL_TAGS {
    public static function check() {
        return [
            'id' => 'design-template-conditional-tags',
            'title' => __('Template Conditional Tags', 'wpshadow'),
            'description' => __('Validates proper use of is_single(), is_archive(), etc.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-template-conditional-tags',
            'training_link' => 'https://wpshadow.com/training/design-template-conditional-tags',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
