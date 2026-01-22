<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Email Preheader
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-preheader
 * Training: https://wpshadow.com/training/design-email-preheader
 */
class Diagnostic_Design_DESIGN_EMAIL_PREHEADER {
    public static function check() {
        return [
            'id' => 'design-email-preheader',
            'title' => __('Email Preheader', 'wpshadow'),
            'description' => __('Checks presence and content of email preheader text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-preheader',
            'training_link' => 'https://wpshadow.com/training/design-email-preheader',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

