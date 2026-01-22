<?php declare(strict_types=1);
/**
 * SoftwareApplication Schema Diagnostic
 *
 * Philosophy: App schema improves discoverability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_SoftwareApplication_Schema {
    public static function check() {
        return [
            'id' => 'seo-softwareapplication-schema',
            'title' => 'SoftwareApplication Schema Markup',
            'description' => 'Add SoftwareApplication schema for software/app products: offers, ratings, features.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/software-schema/',
            'training_link' => 'https://wpshadow.com/training/app-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
