<?php
declare(strict_types=1);
/**
 * Scroll Depth Tracking Diagnostic
 *
 * Philosophy: Scroll depth shows engagement
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Scroll_Depth_Tracking extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-scroll-depth-tracking',
            'title' => 'Scroll Depth Analytics',
            'description' => 'Track scroll depth to understand content engagement and optimize placement.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/scroll-tracking/',
            'training_link' => 'https://wpshadow.com/training/engagement-analytics/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
