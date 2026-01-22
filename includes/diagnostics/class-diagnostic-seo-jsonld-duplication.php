<?php declare(strict_types=1);
/**
 * JSON-LD Duplication Diagnostic
 *
 * Philosophy: Avoid duplicate graphs from multiple sources
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_JSONLD_Duplication {
    public static function check() {
        return [
            'id' => 'seo-jsonld-duplication',
            'title' => 'Avoid Duplicate JSON-LD Graphs',
            'description' => 'Ensure structured data is consolidated into a single coherent JSON-LD graph to prevent duplication/conflicts.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/jsonld-duplication/',
            'training_link' => 'https://wpshadow.com/training/structured-data/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
