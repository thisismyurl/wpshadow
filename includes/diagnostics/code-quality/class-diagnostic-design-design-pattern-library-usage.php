<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pattern Library Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-pattern-library-usage
 * Training: https://wpshadow.com/training/design-pattern-library-usage
 */
class Diagnostic_Design_DESIGN_PATTERN_LIBRARY_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-pattern-library-usage',
            'title' => __('Pattern Library Usage', 'wpshadow'),
            'description' => __('Measures pattern library usage across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-pattern-library-usage',
            'training_link' => 'https://wpshadow.com/training/design-pattern-library-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}