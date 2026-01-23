<?php
declare(strict_types=1);
/**
 * Search Console Verified Diagnostic
 *
 * Philosophy: Monitor indexation and search performance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Search_Console_Verified extends Diagnostic_Base {
    public static function check(): ?array {
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