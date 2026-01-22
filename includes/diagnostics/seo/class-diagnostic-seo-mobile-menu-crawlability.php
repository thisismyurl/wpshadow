<?php
declare(strict_types=1);
/**
 * Mobile Menu Crawlability Diagnostic
 *
 * Philosophy: Ensure nav links are crawlable on mobile
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Mobile_Menu_Crawlability extends Diagnostic_Base {
    public static function check(): ?array {
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
