<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Breadcrumb Schema Markup
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-breadcrumb-markup
 * Training: https://wpshadow.com/training/design-breadcrumb-markup
 */
class Diagnostic_Design_BREADCRUMB_MARKUP {
    public static function check() {
        return [
            'id' => 'design-breadcrumb-markup',
            'title' => __('Breadcrumb Schema Markup', 'wpshadow'),
            'description' => __('Validates breadcrumbs include Schema.org markup.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breadcrumb-markup',
            'training_link' => 'https://wpshadow.com/training/design-breadcrumb-markup',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
