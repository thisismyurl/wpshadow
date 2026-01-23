<?php
declare(strict_types=1);
/**
 * Priority Hints Implementation Diagnostic
 *
 * Philosophy: fetchpriority guides browser
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Priority_Hints_Implementation extends Diagnostic_Base {
    public static function check(): ?array {
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