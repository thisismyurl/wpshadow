<?php
declare(strict_types=1);
/**
 * HSTS Header Configuration Diagnostic
 *
 * Philosophy: HSTS enforces HTTPS
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_HSTS_Header_Configuration extends Diagnostic_Base {
    public static function check(): ?array {
        // Only check if site uses SSL
        if (!is_ssl()) {
            return null; // HSTS only applies to HTTPS sites
        }
        
        // Check for HSTS header
        $response = wp_remote_head(home_url(), array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        
        if (isset($headers['Strict-Transport-Security']) || isset($headers['strict-transport-security'])) {
            return null; // HSTS is configured
        }
        
        return [
            'id' => 'seo-hsts-header-configuration',
            'title' => 'HSTS Header Not Configured',
            'description' => 'HTTP Strict Transport Security (HSTS) header is missing. Enable to enforce HTTPS.',
            'severity' => 'medium',
            'category' => 'security',
            'kb_link' => 'https://wpshadow.com/kb/hsts/',
            'training_link' => 'https://wpshadow.com/training/https-enforcement/',
            'auto_fixable' => true,
            'threat_level' => 65,
        ];
    }
}
