<?php declare(strict_types=1);
/**
 * Secure Cookie Configuration Diagnostic
 *
 * Philosophy: Secure cookies prevent hijacking
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Secure_Cookie_Configuration {
    public static function check() {
        return [
            'id' => 'seo-secure-cookie-configuration',
            'title' => 'Secure Cookie Attributes',
            'description' => 'Set Secure, HttpOnly, and SameSite attributes on cookies for protection.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/cookie-security/',
            'training_link' => 'https://wpshadow.com/training/session-security/',
            'auto_fixable' => false,
            'threat_level' => 60,
        ];
    }
}
