<?php
declare(strict_types=1);
/**
 * Link Headers Preload Diagnostic
 *
 * Philosophy: Link headers enable early resource hints
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Link_Headers_Preload extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-link-headers-preload',
            'title' => 'Link Headers for Resource Hints',
            'description' => 'Use Link HTTP headers for preload, preconnect, and prefetch to improve loading performance.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/link-headers/',
            'training_link' => 'https://wpshadow.com/training/resource-hints/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
