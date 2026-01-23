<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Display Missing
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-font-display-missing
 * Training: https://wpshadow.com/training/design-font-display-missing
 */
class Diagnostic_Design_DESIGN_FONT_DISPLAY_MISSING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-display-missing',
            'title' => __('Font Display Missing', 'wpshadow'),
            'description' => __('Flags fonts lacking font-display settings.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-display-missing',
            'training_link' => 'https://wpshadow.com/training/design-font-display-missing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}