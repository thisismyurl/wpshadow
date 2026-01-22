<?php declare(strict_types=1);
/**
 * Social Sharing Analytics Diagnostic
 *
 * Philosophy: Track what content gets shared
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Social_Sharing_Analytics {
    public static function check() {
        return [
            'id' => 'seo-social-sharing-analytics',
            'title' => 'Social Sharing Tracking',
            'description' => 'Track social shares to identify high-performing content and optimize for sharing.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/social-analytics/',
            'training_link' => 'https://wpshadow.com/training/social-media-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
