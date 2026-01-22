<?php declare(strict_types=1);
/**
 * Mobile Desktop Content Parity Diagnostic
 *
 * Philosophy: Mobile and desktop should show same content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Mobile_Desktop_Content_Parity {
    public static function check() {
        return [
            'id' => 'seo-mobile-desktop-content-parity',
            'title' => 'Mobile-Desktop Content Parity',
            'description' => 'Ensure mobile version shows the same content as desktop. Hidden mobile content may not be indexed.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/mobile-content-parity/',
            'training_link' => 'https://wpshadow.com/training/mobile-first-indexing/',
            'auto_fixable' => false,
            'threat_level' => 75,
        ];
    }
}
