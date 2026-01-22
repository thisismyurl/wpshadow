<?php declare(strict_types=1);
/**
 * VideoObject Schema Diagnostic
 *
 * Philosophy: Capture video signals with structured data
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_VideoObject_Schema {
    public static function check() {
        return [
            'id' => 'seo-videoobject-schema',
            'title' => 'VideoObject Schema Presence',
            'description' => 'Add VideoObject structured data to pages with embedded videos to enable rich results.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/videoobject-schema/',
            'training_link' => 'https://wpshadow.com/training/video-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
