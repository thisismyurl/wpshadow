<?php declare(strict_types=1);
/**
 * Font Display Swap Diagnostic
 *
 * Philosophy: Avoid FOIT by using font-display: swap
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Font_Display_Swap {
    public static function check() {
        return [
            'id' => 'seo-font-display-swap',
            'title' => 'Use font-display: swap',
            'description' => 'Set font-display: swap for web fonts to improve perceived performance and text visibility.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/font-display-swap/',
            'training_link' => 'https://wpshadow.com/training/performance-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
