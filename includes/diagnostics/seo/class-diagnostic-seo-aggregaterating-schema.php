<?php
declare(strict_types=1);
/**
 * AggregateRating Schema Diagnostic
 *
 * Philosophy: Aggregate ratings build trust
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_AggregateRating_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-aggregaterating-schema',
            'title' => 'AggregateRating Schema Completeness',
            'description' => 'Add AggregateRating schema: ratingValue, reviewCount, bestRating for star displays.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/rating-schema/',
            'training_link' => 'https://wpshadow.com/training/review-markup/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }

}