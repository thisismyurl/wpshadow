<?php declare(strict_types=1);
/**
 * Conversion Tracking Diagnostic
 *
 * Philosophy: Track key events for optimization
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Conversion_Tracking {
    public static function check() {
        return [
            'id' => 'seo-conversion-tracking',
            'title' => 'Conversion Tracking Setup',
            'description' => 'Ensure key conversion events (form submissions, purchases, signups) are instrumented for tracking.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/conversion-tracking/',
            'training_link' => 'https://wpshadow.com/training/analytics-setup/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
