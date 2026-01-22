<?php declare(strict_types=1);
/**
 * Canonical vs Hreflang Consistency Diagnostic
 *
 * Philosophy: Ensure canonical and hreflang agree
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Canonical_Hreflang_Consistency {
    public static function check() {
        return [
            'id' => 'seo-canonical-hreflang-consistency',
            'title' => 'Canonical vs Hreflang Consistency',
            'description' => 'Ensure canonical URLs and hreflang alternates are mutually consistent to avoid indexation conflicts.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/canonical-hreflang-consistency/',
            'training_link' => 'https://wpshadow.com/training/international-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
