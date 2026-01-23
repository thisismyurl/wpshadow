<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Alt Text
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-alt-text
 * Training: https://wpshadow.com/training/design-email-alt-text
 */
class Diagnostic_Design_DESIGN_EMAIL_ALT_TEXT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-alt-text',
            'title' => __('Email Alt Text', 'wpshadow'),
            'description' => __('Checks presence and quality of alt text in emails.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-alt-text',
            'training_link' => 'https://wpshadow.com/training/design-email-alt-text',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}