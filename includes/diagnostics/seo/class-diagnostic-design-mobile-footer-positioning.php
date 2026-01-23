<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Mobile Footer Positioning
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-footer-positioning
 * Training: https://wpshadow.com/training/design-mobile-footer-positioning
 */
class Diagnostic_Design_MOBILE_FOOTER_POSITIONING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-mobile-footer-positioning',
            'title' => __('Mobile Footer Positioning', 'wpshadow'),
            'description' => __('Checks footer positioning on mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-footer-positioning',
            'training_link' => 'https://wpshadow.com/training/design-mobile-footer-positioning',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}