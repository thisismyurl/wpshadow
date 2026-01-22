<?php
declare(strict_types=1);
/**
 * Map Embedding Diagnostic
 *
 * Philosophy: Use crawlable address markup, not image-only maps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Map_Embedding extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-map-embedding',
            'title' => 'Map Embedding Best Practices',
            'description' => 'Ensure map embeds include crawlable address markup alongside visual map (not image-only).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/map-embedding/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
