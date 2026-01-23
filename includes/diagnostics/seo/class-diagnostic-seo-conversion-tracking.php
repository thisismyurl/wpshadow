<?php
declare(strict_types=1);
/**
 * Conversion Tracking Diagnostic
 *
 * Philosophy: Track key events for optimization
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Conversion_Tracking extends Diagnostic_Base {
    public static function check(): ?array {
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