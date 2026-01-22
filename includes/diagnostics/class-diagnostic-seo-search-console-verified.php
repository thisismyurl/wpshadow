<?php declare(strict_types=1);
/**
 * Search Console Verified Diagnostic
 *
 * Philosophy: Monitor indexation and search performance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Search_Console_Verified {
    public static function check() {
        return [
            'id' => 'seo-search-console-verified',
            'title' => 'Search Console Verification',
            'description' => 'Verify site ownership in Google Search Console to monitor indexation, performance, and technical issues.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/search-console-setup/',
            'training_link' => 'https://wpshadow.com/training/search-console/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
