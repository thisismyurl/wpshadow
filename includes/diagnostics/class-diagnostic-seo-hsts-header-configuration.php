<?php declare(strict_types=1);
/**
 * HSTS Header Configuration Diagnostic
 *
 * Philosophy: HSTS enforces HTTPS
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_HSTS_Header_Configuration {
    public static function check() {
        return [
            'id' => 'seo-hsts-header-configuration',
            'title' => 'HTTP Strict Transport Security (HSTS)',
            'description' => 'Enable HSTS header to enforce HTTPS and prevent downgrade attacks.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/hsts/',
            'training_link' => 'https://wpshadow.com/training/https-enforcement/',
            'auto_fixable' => false,
            'threat_level' => 65,
        ];
    }
}
