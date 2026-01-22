<?php declare(strict_types=1);
/**
 * CSP Header Implementation Diagnostic
 *
 * Philosophy: CSP prevents XSS attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_CSP_Header_Implementation {
    public static function check() {
        return [
            'id' => 'seo-csp-header-implementation',
            'title' => 'Content Security Policy (CSP)',
            'description' => 'Implement Content-Security-Policy header to prevent XSS and injection attacks.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/csp-header/',
            'training_link' => 'https://wpshadow.com/training/security-headers/',
            'auto_fixable' => false,
            'threat_level' => 70,
        ];
    }
}
