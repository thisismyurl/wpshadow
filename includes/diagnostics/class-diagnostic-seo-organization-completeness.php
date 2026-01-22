<?php declare(strict_types=1);
/**
 * Organization Completeness Diagnostic
 *
 * Philosophy: Complete Organization/Person schema
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Organization_Completeness {
    public static function check() {
        return [
            'id' => 'seo-organization-completeness',
            'title' => 'Organization Schema Completeness',
            'description' => 'Ensure Organization or Person schema includes logo, url, and contactPoint for entity recognition.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/organization-schema/',
            'training_link' => 'https://wpshadow.com/training/entity-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
