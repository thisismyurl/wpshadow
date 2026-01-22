<?php
declare(strict_types=1);
/**
 * CSP Header Implementation Diagnostic
 *
 * Philosophy: CSP prevents XSS attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_CSP_Header_Implementation extends Diagnostic_Base {
    public static function check(): ?array {
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
