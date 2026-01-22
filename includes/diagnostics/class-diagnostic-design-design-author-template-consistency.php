<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Author Template Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-author-template-consistency
 * Training: https://wpshadow.com/training/design-author-template-consistency
 */
class Diagnostic_Design_DESIGN_AUTHOR_TEMPLATE_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-author-template-consistency',
            'title' => __('Author Template Consistency', 'wpshadow'),
            'description' => __('Checks author pages inherit global styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-author-template-consistency',
            'training_link' => 'https://wpshadow.com/training/design-author-template-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

