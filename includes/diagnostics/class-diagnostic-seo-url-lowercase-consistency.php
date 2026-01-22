<?php declare(strict_types=1);
/**
 * URL Lowercase Consistency Diagnostic
 *
 * Philosophy: Normalize URL case for canonicalization
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_URL_Lowercase_Consistency {
    public static function check() {
        return [
            'id' => 'seo-url-lowercase-consistency',
            'title' => 'Lowercase URL Consistency',
            'description' => 'Ensure URLs are normalized to lowercase to avoid duplicate paths differing only by case.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/url-normalization/',
            'training_link' => 'https://wpshadow.com/training/url-canonicalization/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
