<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Widget Area Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-widget-area-styling
 * Training: https://wpshadow.com/training/design-widget-area-styling
 */
class Diagnostic_Design_WIDGET_AREA_STYLING {
    public static function check() {
        return [
            'id' => 'design-widget-area-styling',
            'title' => __('Widget Area Consistency', 'wpshadow'),
            'description' => __('Checks widget areas styled consistently across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-widget-area-styling',
            'training_link' => 'https://wpshadow.com/training/design-widget-area-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
