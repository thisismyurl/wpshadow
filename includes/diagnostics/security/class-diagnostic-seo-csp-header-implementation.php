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
        // Check for CSP header
        $response = wp_remote_head(home_url(), array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        
        if (isset($headers['Content-Security-Policy']) || 
            isset($headers['content-security-policy']) ||
            isset($headers['Content-Security-Policy-Report-Only']) ||
            isset($headers['content-security-policy-report-only'])) {
            return null; // CSP is configured
        }
        
        return [
            'id' => 'seo-csp-header-implementation',
            'title' => 'Content Security Policy Not Configured',
            'description' => 'Content-Security-Policy (CSP) header missing. Implement to prevent XSS attacks.',
            'severity' => 'medium',
            'category' => 'security',
            'kb_link' => 'https://wpshadow.com/kb/csp-header/',
            'training_link' => 'https://wpshadow.com/training/security-headers/',
            'auto_fixable' => false,
            'threat_level' => 70,
        ];
    }
}
