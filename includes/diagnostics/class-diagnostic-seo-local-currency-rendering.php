<?php declare(strict_types=1);
/**
 * Local Currency Rendering Diagnostic
 *
 * Philosophy: Use locale-appropriate currency display
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Local_Currency_Rendering {
    public static function check() {
        return [
            'id' => 'seo-local-currency-rendering',
            'title' => 'Local Currency Rendering',
            'description' => 'Ensure e-commerce pages render prices in appropriate currency and format for the locale.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/local-currency-rendering/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
