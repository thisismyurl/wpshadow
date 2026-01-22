<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email List Unstyled
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-list-unstyled
 * Training: https://wpshadow.com/training/design-email-list-unstyled
 */
class Diagnostic_Design_DESIGN_EMAIL_LIST_UNSTYLED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-list-unstyled',
            'title' => __('Email List Unstyled', 'wpshadow'),
            'description' => __('Prevents unstyled list bullets in email clients.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-list-unstyled',
            'training_link' => 'https://wpshadow.com/training/design-email-list-unstyled',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
