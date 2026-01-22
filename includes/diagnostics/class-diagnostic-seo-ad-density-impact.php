<?php declare(strict_types=1);
/**
 * Ad Density Impact Diagnostic
 *
 * Philosophy: Too many ads hurt UX
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Ad_Density_Impact {
    public static function check() {
        return [
            'id' => 'seo-ad-density-impact',
            'title' => 'Advertisement Density',
            'description' => 'Limit ads above-the-fold and maintain reasonable ad-to-content ratio for better UX.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ad-density/',
            'training_link' => 'https://wpshadow.com/training/monetization-ux/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
