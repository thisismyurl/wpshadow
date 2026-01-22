<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: rAF Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-raf-usage
 * Training: https://wpshadow.com/training/design-raf-usage
 */
class Diagnostic_Design_DESIGN_RAF_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-raf-usage',
            'title' => __('rAF Usage', 'wpshadow'),
            'description' => __('Checks animations use requestAnimationFrame instead of timers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-raf-usage',
            'training_link' => 'https://wpshadow.com/training/design-raf-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
