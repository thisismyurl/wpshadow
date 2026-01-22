<?php
declare(strict_types=1);
/**
 * Movie/TVSeries Schema Diagnostic
 *
 * Philosophy: Movie schema for entertainment content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Movie_TVSeries_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-movie-tvseries-schema',
            'title' => 'Movie/TVSeries Schema Markup',
            'description' => 'Add Movie/TVSeries schema for entertainment content: cast, director, reviews.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/movie-schema/',
            'training_link' => 'https://wpshadow.com/training/entertainment-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
