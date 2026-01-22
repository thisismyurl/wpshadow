<?php declare(strict_types=1);
/**
 * Priority Hints Implementation Diagnostic
 *
 * Philosophy: fetchpriority guides browser
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Priority_Hints_Implementation {
    public static function check() {
        return [
            'id' => 'seo-priority-hints-implementation',
            'title' => 'Priority Hints (fetchpriority)',
            'description' => 'Use fetchpriority="high" on LCP images and fetchpriority="low" on non-critical resources.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/priority-hints/',
            'training_link' => 'https://wpshadow.com/training/resource-prioritization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
