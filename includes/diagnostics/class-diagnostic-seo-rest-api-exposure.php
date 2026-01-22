<?php declare(strict_types=1);
/**
 * REST API Exposure Diagnostic
 *
 * Philosophy: Limit unnecessary API endpoints
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_REST_API_Exposure {
    public static function check() {
        return [
            'id' => 'seo-rest-api-exposure',
            'title' => 'REST API Endpoint Exposure',
            'description' => 'Review REST API endpoints and disable unnecessary ones to reduce attack surface and info leakage.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/rest-api-security/',
            'training_link' => 'https://wpshadow.com/training/wordpress-security/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
