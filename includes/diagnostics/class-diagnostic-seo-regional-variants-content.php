<?php declare(strict_types=1);
/**
 * Regional Variants Content Diagnostic
 *
 * Philosophy: Localized content for en-US vs en-GB, etc.
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Regional_Variants_Content {
    public static function check() {
        return [
            'id' => 'seo-regional-variants-content',
            'title' => 'Regional Content Variants',
            'description' => 'For multi-region sites, ensure content differences (spelling, currency, etc.) are reflected per region.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/regional-content-variants/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
