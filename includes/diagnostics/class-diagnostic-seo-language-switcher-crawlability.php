<?php declare(strict_types=1);
/**
 * Language Switcher Crawlability Diagnostic
 *
 * Philosophy: Make language switch links crawlable
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Language_Switcher_Crawlability {
    public static function check() {
        return [
            'id' => 'seo-language-switcher-crawlability',
            'title' => 'Language Switcher Crawlability',
            'description' => 'Ensure language switcher uses anchor links and is crawl-friendly, not JS-only navigation.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/language-switcher-crawlability/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
