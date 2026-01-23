<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Responsive Layout
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-responsive
 * Training: https://wpshadow.com/training/design-email-responsive
 */
class Diagnostic_Design_DESIGN_EMAIL_RESPONSIVE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-responsive',
            'title' => __('Email Responsive Layout', 'wpshadow'),
            'description' => __('Checks media queries and widths for mobile email clients.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-responsive',
            'training_link' => 'https://wpshadow.com/training/design-email-responsive',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}