<?php declare(strict_types=1);
/**
 * Referrer-Policy Header Diagnostic
 *
 * Philosophy: Control referrer information leakage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Referrer_Policy_Header {
    public static function check() {
        return [
            'id' => 'seo-referrer-policy-header',
            'title' => 'Referrer-Policy Header',
            'description' => 'Set Referrer-Policy to strict-origin-when-cross-origin for privacy and security.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/referrer-policy/',
            'training_link' => 'https://wpshadow.com/training/privacy-headers/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }
}
