<?php declare(strict_types=1);
/**
 * Scroll Jank Risk Diagnostic
 *
 * Philosophy: Maintain smooth scrolling for good UX
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Scroll_Jank_Risk {
    public static function check() {
        return [
            'id' => 'seo-scroll-jank-risk',
            'title' => 'Scroll Jank Risk',
            'description' => 'Avoid heavy fixed elements and expensive scroll handlers that cause jank on mobile.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/scroll-jank/',
            'training_link' => 'https://wpshadow.com/training/mobile-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
