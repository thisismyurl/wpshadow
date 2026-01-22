<?php declare(strict_types=1);
/**
 * Duplicate Locations Diagnostic
 *
 * Philosophy: Unique pages per location for multi-location businesses
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Duplicate_Locations {
    public static function check() {
        return [
            'id' => 'seo-duplicate-locations',
            'title' => 'Unique Pages per Location',
            'description' => 'Multi-location businesses should have unique pages per location with distinct content and schema.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/multi-location-seo/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
