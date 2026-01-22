<?php declare(strict_types=1);
/**
 * Hreflang x-default Diagnostic
 *
 * Philosophy: International targeting completeness
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Hreflang_X_Default {
    /**
     * Advisory: ensure x-default hreflang is present when alternates exist.
     *
     * @return array|null
     */
    public static function check() {
        return [
            'id' => 'seo-hreflang-x-default',
            'title' => 'Add x-default Hreflang for Alternates',
            'description' => 'When multiple language/region alternates exist, include x-default hreflang to signal the default page.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/hreflang-x-default/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
