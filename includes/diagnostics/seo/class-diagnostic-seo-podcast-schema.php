<?php
declare(strict_types=1);
/**
 * Podcast Schema Diagnostic
 *
 * Philosophy: Podcast schema improves podcast discovery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Podcast_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-podcast-schema',
            'title' => 'Podcast Schema Markup',
            'description' => 'Add PodcastSeries/PodcastEpisode schema: host, duration, audio file.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/podcast-schema/',
            'training_link' => 'https://wpshadow.com/training/podcast-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}