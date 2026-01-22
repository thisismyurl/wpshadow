<?php declare(strict_types=1);
/**
 * Opening Hours Schema Diagnostic
 *
 * Philosophy: Provide structured business hours
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Opening_Hours_Schema {
    public static function check() {
        return [
            'id' => 'seo-opening-hours-schema',
            'title' => 'Opening Hours Schema',
            'description' => 'Add openingHoursSpecification to LocalBusiness schema for clear business hour signals.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/opening-hours-schema/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
