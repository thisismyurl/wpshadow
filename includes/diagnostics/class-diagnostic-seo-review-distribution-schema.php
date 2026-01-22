<?php declare(strict_types=1);
/**
 * Review Distribution Schema Diagnostic
 *
 * Philosophy: Review schema enhances credibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Review_Distribution_Schema {
    public static function check() {
        return [
            'id' => 'seo-review-distribution-schema',
            'title' => 'Review Schema Implementation',
            'description' => 'Add Review schema for user reviews: reviewer, rating, reviewBody.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/review-schema/',
            'training_link' => 'https://wpshadow.com/training/ugc-markup/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
