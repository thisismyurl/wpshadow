<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Inline CSS Hygiene
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-inline-css-hygiene
 * Training: https://wpshadow.com/training/design-email-inline-css-hygiene
 */
class Diagnostic_Design_DESIGN_EMAIL_INLINE_CSS_HYGIENE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-inline-css-hygiene',
            'title' => __('Email Inline CSS Hygiene', 'wpshadow'),
            'description' => __('Checks inline CSS usage and consistency in email.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-inline-css-hygiene',
            'training_link' => 'https://wpshadow.com/training/design-email-inline-css-hygiene',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
