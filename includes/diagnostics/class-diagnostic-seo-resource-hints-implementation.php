<?php declare(strict_types=1);
/**
 * Resource Hints Implementation Diagnostic
 *
 * Philosophy: dns-prefetch and preconnect help
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Resource_Hints_Implementation {
    public static function check() {
        return [
            'id' => 'seo-resource-hints-implementation',
            'title' => 'Resource Hints (dns-prefetch, preconnect)',
            'description' => 'Implement dns-prefetch and preconnect for external resources like fonts, CDNs, APIs.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/resource-hints/',
            'training_link' => 'https://wpshadow.com/training/performance-optimization/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
