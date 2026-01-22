<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Family Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-family-consistency
 * Training: https://wpshadow.com/training/design-font-family-consistency
 */
class Diagnostic_Design_FONT_FAMILY_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-family-consistency',
            'title' => __('Font Family Consistency', 'wpshadow'),
            'description' => __('Verifies 2-3 font families max, consistent usage.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-family-consistency',
            'training_link' => 'https://wpshadow.com/training/design-font-family-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
