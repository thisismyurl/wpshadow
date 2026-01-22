<?php
declare(strict_types=1);
/**
 * Nofollow Internal Misuse Diagnostic
 *
 * Philosophy: Avoid nofollow on own pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Nofollow_Internal_Misuse extends Diagnostic_Base {
    public static function check(): ?array {
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
