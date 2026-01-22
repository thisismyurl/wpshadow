<?php declare(strict_types=1);
/**
 * Cache-Control Headers Diagnostic
 *
 * Philosophy: Cache-Control directives control caching
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Cache_Control_Headers {
    public static function check() {
        return [
            'id' => 'seo-cache-control-headers',
            'title' => 'Cache-Control Header Optimization',
            'description' => 'Set appropriate Cache-Control directives: max-age, public/private, immutable for static assets.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/cache-control/',
            'training_link' => 'https://wpshadow.com/training/caching-strategies/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
