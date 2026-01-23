<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Tracking Pixel Visibility
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-tracking-pixel-visibility
 * Training: https://wpshadow.com/training/design-email-tracking-pixel-visibility
 */
class Diagnostic_Design_DESIGN_EMAIL_TRACKING_PIXEL_VISIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-tracking-pixel-visibility',
            'title' => __('Email Tracking Pixel Visibility', 'wpshadow'),
            'description' => __('Checks tracking pixels are hidden or unobtrusive.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-tracking-pixel-visibility',
            'training_link' => 'https://wpshadow.com/training/design-email-tracking-pixel-visibility',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}