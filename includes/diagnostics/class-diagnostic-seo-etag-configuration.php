<?php declare(strict_types=1);
/**
 * ETag Configuration Diagnostic
 *
 * Philosophy: ETags enable efficient caching
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_ETag_Configuration {
    public static function check() {
        return [
            'id' => 'seo-etag-configuration',
            'title' => 'ETag Configuration for Caching',
            'description' => 'Configure ETags properly for efficient browser caching and conditional requests.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/etag-caching/',
            'training_link' => 'https://wpshadow.com/training/http-caching/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
