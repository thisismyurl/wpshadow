<?php declare(strict_types=1);
/**
 * Mobile Redirect Strategy Diagnostic
 *
 * Philosophy: Separate mobile URLs need proper configuration
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Mobile_Redirect_Strategy {
    public static function check() {
        return [
            'id' => 'seo-mobile-redirect-strategy',
            'title' => 'Mobile Redirect Configuration',
            'description' => 'If using separate mobile URLs (m.example.com), implement proper redirects and annotations.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/mobile-redirects/',
            'training_link' => 'https://wpshadow.com/training/mobile-url-structure/',
            'auto_fixable' => false,
            'threat_level' => 50,
        ];
    }
}
