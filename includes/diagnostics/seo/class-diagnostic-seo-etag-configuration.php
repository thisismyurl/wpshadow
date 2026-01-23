<?php
declare(strict_types=1);
/**
 * ETag Configuration Diagnostic
 *
 * Philosophy: ETags enable efficient caching
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_ETag_Configuration extends Diagnostic_Base {
    public static function check(): ?array {
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