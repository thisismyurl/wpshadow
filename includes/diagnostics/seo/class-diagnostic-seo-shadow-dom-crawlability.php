<?php
declare(strict_types=1);
/**
 * Shadow DOM Crawlability Diagnostic
 *
 * Philosophy: Shadow DOM content may not be indexed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Shadow_DOM_Crawlability extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-shadow-dom-crawlability',
            'title' => 'Shadow DOM Content Indexability',
            'description' => 'Shadow DOM content may not be indexed. Verify with Search Console or use declarative shadow DOM.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/shadow-dom-seo/',
            'training_link' => 'https://wpshadow.com/training/web-components-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
