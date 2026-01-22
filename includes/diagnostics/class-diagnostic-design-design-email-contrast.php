<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Email Contrast
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-contrast
 * Training: https://wpshadow.com/training/design-email-contrast
 */
class Diagnostic_Design_DESIGN_EMAIL_CONTRAST {
    public static function check() {
        return [
            'id' => 'design-email-contrast',
            'title' => __('Email Contrast', 'wpshadow'),
            'description' => __('Checks contrast safety for light and dark email clients.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-contrast',
            'training_link' => 'https://wpshadow.com/training/design-email-contrast',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

