<?php
declare(strict_types=1);
/**
 * Internal Link Depth Diagnostic
 *
 * Philosophy: Key pages within 3 clicks for crawlability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Internal_Link_Depth extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-internal-link-depth',
            'title' => 'Internal Link Depth',
            'description' => 'Ensure important pages are reachable within 3 clicks from the homepage for optimal crawl efficiency.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/link-depth/',
            'training_link' => 'https://wpshadow.com/training/site-architecture/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
