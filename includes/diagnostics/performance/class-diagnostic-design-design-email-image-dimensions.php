<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Image Dimensions
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-image-dimensions
 * Training: https://wpshadow.com/training/design-email-image-dimensions
 */
class Diagnostic_Design_DESIGN_EMAIL_IMAGE_DIMENSIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-image-dimensions',
            'title' => __('Email Image Dimensions', 'wpshadow'),
            'description' => __('Checks image sizing and fallbacks in email templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-image-dimensions',
            'training_link' => 'https://wpshadow.com/training/design-email-image-dimensions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
