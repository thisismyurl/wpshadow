<?php declare(strict_types=1);
/**
 * Subresource Integrity (SRI) Diagnostic
 *
 * Philosophy: SRI protects against compromised CDNs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Subresource_Integrity {
    public static function check() {
        return [
            'id' => 'seo-subresource-integrity',
            'title' => 'Subresource Integrity (SRI)',
            'description' => 'Add integrity attributes to CDN resources for security and trust signals.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/subresource-integrity/',
            'training_link' => 'https://wpshadow.com/training/cdn-security/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
