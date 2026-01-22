<?php declare(strict_types=1);
/**
 * HTTP Requests Count Audit Diagnostic
 *
 * Philosophy: Reduce excessive requests for faster loads
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_HTTP_Requests_Count_Audit {
    public static function check() {
        return [
            'id' => 'seo-http-requests-count-audit',
            'title' => 'HTTP Requests Count Audit',
            'description' => 'Audit and reduce the number of HTTP requests on critical templates to improve speed and crawl efficiency.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/http-requests-optimization/',
            'training_link' => 'https://wpshadow.com/training/performance-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
