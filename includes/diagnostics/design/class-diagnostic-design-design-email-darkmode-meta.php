<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Dark Mode Meta
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-darkmode-meta
 * Training: https://wpshadow.com/training/design-email-darkmode-meta
 */
class Diagnostic_Design_DESIGN_EMAIL_DARKMODE_META extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-darkmode-meta',
            'title' => __('Email Dark Mode Meta', 'wpshadow'),
            'description' => __('Checks dark mode meta and color safety in email.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-darkmode-meta',
            'training_link' => 'https://wpshadow.com/training/design-email-darkmode-meta',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}