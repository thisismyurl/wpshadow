<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email CTA Tap Target
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-cta-tap-target
 * Training: https://wpshadow.com/training/design-email-cta-tap-target
 */
class Diagnostic_Design_DESIGN_EMAIL_CTA_TAP_TARGET extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-cta-tap-target',
            'title' => __('Email CTA Tap Target', 'wpshadow'),
            'description' => __('Checks CTA button sizing for mobile email.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-cta-tap-target',
            'training_link' => 'https://wpshadow.com/training/design-email-cta-tap-target',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}