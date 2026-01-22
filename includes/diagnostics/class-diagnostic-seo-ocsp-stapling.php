<?php declare(strict_types=1);
/**
 * OCSP Stapling Diagnostic
 *
 * Philosophy: Stapling improves SSL performance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_OCSP_Stapling {
    public static function check() {
        return [
            'id' => 'seo-ocsp-stapling',
            'title' => 'OCSP Stapling Enabled',
            'description' => 'Enable OCSP stapling to reduce SSL handshake time by caching certificate validation.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ocsp-stapling/',
            'training_link' => 'https://wpshadow.com/training/ssl-optimization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
