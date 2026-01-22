<?php declare(strict_types=1);
/**
 * Breadcrumb Click Analytics Diagnostic
 *
 * Philosophy: Breadcrumb usage shows navigation patterns
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Breadcrumb_Click_Analytics {
    public static function check() {
        return [
            'id' => 'seo-breadcrumb-click-analytics',
            'title' => 'Breadcrumb Navigation Analytics',
            'description' => 'Track breadcrumb clicks to understand how users navigate site hierarchy.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/breadcrumb-analytics/',
            'training_link' => 'https://wpshadow.com/training/navigation-tracking/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
