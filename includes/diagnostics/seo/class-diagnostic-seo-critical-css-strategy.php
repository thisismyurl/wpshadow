<?php
declare(strict_types=1);
/**
 * Critical CSS Strategy Diagnostic
 *
 * Philosophy: Inline above-the-fold CSS for speed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Critical_CSS_Strategy extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-critical-css-strategy',
            'title' => 'Critical CSS Strategy',
            'description' => 'Implement a critical CSS strategy to inline above-the-fold styles and defer the rest.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/critical-css/',
            'training_link' => 'https://wpshadow.com/training/performance-seo/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
