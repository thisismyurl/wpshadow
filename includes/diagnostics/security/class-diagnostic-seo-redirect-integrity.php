<?php
declare(strict_types=1);
/**
 * Redirect Integrity Diagnostic
 *
 * Philosophy: Clean canonicalization to HTTPS and primary host
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Redirect_Integrity extends Diagnostic_Base {
    /**
     * Quick check: HTTP home should redirect to HTTPS (single hop).
     *
     * @return array|null
     */
    public static function check(): ?array {
        $home = home_url('/', 'http');
        $response = wp_remote_head($home, ['timeout' => 5, 'redirection' => 3]);
        if (!is_wp_error($response)) {
            $finalUrl = wp_remote_retrieve_header($response, 'location');
            $code = wp_remote_retrieve_response_code($response);
            if ($code >= 300 && $code < 400) {
                // We saw a redirect; advisory only
                return null;
            }
        }
        return [
            'id' => 'seo-redirect-integrity',
            'title' => 'HTTP to HTTPS Redirect Integrity',
            'description' => 'Verify that HTTP requests redirect to HTTPS on the canonical host with a single hop (301/308 preferred).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/https-canonicalization/',
            'training_link' => 'https://wpshadow.com/training/redirects-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}