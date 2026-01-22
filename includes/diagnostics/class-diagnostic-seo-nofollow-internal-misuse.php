<?php declare(strict_types=1);
/**
 * Nofollow Internal Misuse Diagnostic
 *
 * Philosophy: Avoid nofollow on own pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Nofollow_Internal_Misuse {
    public static function check() {
        return [
            'id' => 'seo-nofollow-internal-misuse',
            'title' => 'Nofollow Internal Links Misuse',
            'description' => 'Avoid using nofollow on internal links; reserve it for untrusted external links.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/nofollow-internal-links/',
            'training_link' => 'https://wpshadow.com/training/internal-linking/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
