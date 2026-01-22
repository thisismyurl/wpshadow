<?php
declare(strict_types=1);
/**
 * Language Switcher Crawlability Diagnostic
 *
 * Philosophy: Make language switch links crawlable
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Language_Switcher_Crawlability extends Diagnostic_Base {
    public static function check(): ?array {
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
