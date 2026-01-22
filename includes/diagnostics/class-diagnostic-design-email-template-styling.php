<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Email Template Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-template-styling
 * Training: https://wpshadow.com/training/design-email-template-styling
 */
class Diagnostic_Design_EMAIL_TEMPLATE_STYLING {
    public static function check() {
        return [
            'id' => 'design-email-template-styling',
            'title' => __('Email Template Styling', 'wpshadow'),
            'description' => __('Validates WordPress email templates styled.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-template-styling',
            'training_link' => 'https://wpshadow.com/training/design-email-template-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
