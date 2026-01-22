<?php declare(strict_types=1);
/**
 * SSL Handshake Time Diagnostic
 *
 * Philosophy: TLS negotiation impacts performance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_SSL_Handshake_Time {
    public static function check() {
        return [
            'id' => 'seo-ssl-handshake-time',
            'title' => 'SSL/TLS Handshake Duration',
            'description' => 'SSL handshake should complete under 100ms. Use TLS 1.3, enable session resumption, and consider OCSP stapling.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ssl-performance/',
            'training_link' => 'https://wpshadow.com/training/https-optimization/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
