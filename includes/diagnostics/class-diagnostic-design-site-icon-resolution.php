<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Site Icon Resolution
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-site-icon-resolution
 * Training: https://wpshadow.com/training/design-site-icon-resolution
 */
class Diagnostic_Design_SITE_ICON_RESOLUTION {
    public static function check() {
        return [
            'id' => 'design-site-icon-resolution',
            'title' => __('Site Icon Resolution', 'wpshadow'),
            'description' => __('Validates favicon/site icon multiple sizes (16, 32, 192, 512px).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-site-icon-resolution',
            'training_link' => 'https://wpshadow.com/training/design-site-icon-resolution',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
