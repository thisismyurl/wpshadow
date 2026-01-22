<?php declare(strict_types=1);
/**
 * Review Schema Adherence Diagnostic
 *
 * Philosophy: Ensure authentic reviews with proper markup
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Review_Schema_Adherence {
    public static function check() {
        return [
            'id' => 'seo-review-schema-adherence',
            'title' => 'Review Schema Adherence',
            'description' => 'Ensure review markup represents authentic user reviews, not self-promotional or spam patterns.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/review-schema-guidelines/',
            'training_link' => 'https://wpshadow.com/training/schema-serp-features/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
