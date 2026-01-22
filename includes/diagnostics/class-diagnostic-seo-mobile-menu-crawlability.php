<?php declare(strict_types=1);
/**
 * Mobile Menu Crawlability Diagnostic
 *
 * Philosophy: Ensure nav links are crawlable on mobile
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Mobile_Menu_Crawlability {
    public static function check() {
        return [
            'id' => 'seo-mobile-menu-crawlability',
            'title' => 'Mobile Menu Crawlability',
            'description' => 'Make sure mobile navigation uses real anchor links (not JS-only) so crawlers can discover content.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/mobile-menu-crawlability/',
            'training_link' => 'https://wpshadow.com/training/mobile-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
