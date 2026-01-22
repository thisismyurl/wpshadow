<?php declare(strict_types=1);
/**
 * Broken Internal Links Diagnostic
 *
 * Philosophy: Fix internal 404 targets promptly
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Broken_Internal_Links {
    public static function check() {
        return [
            'id' => 'seo-broken-internal-links',
            'title' => 'Broken Internal Links',
            'description' => 'Identify and fix internal links pointing to 404 pages to maintain link equity and UX.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/broken-internal-links/',
            'training_link' => 'https://wpshadow.com/training/link-maintenance/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
