<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Scroll Handler Throttling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-scroll-handler-throttling
 * Training: https://wpshadow.com/training/design-scroll-handler-throttling
 */
class Diagnostic_Design_DESIGN_SCROLL_HANDLER_THROTTLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-scroll-handler-throttling',
            'title' => __('Scroll Handler Throttling', 'wpshadow'),
            'description' => __('Checks scroll and resize listeners for throttling or debouncing.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-scroll-handler-throttling',
            'training_link' => 'https://wpshadow.com/training/design-scroll-handler-throttling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}