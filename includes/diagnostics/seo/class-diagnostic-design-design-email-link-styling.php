<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Link Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-link-styling
 * Training: https://wpshadow.com/training/design-email-link-styling
 */
class Diagnostic_Design_DESIGN_EMAIL_LINK_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-link-styling',
            'title' => __('Email Link Styling', 'wpshadow'),
            'description' => __('Checks link visibility, contrast, and hover styling in emails.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-link-styling',
            'training_link' => 'https://wpshadow.com/training/design-email-link-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}