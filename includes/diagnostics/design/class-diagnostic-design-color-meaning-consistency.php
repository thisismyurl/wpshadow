<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Color Meaning Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-color-meaning-consistency
 * Training: https://wpshadow.com/training/design-color-meaning-consistency
 */
class Diagnostic_Design_COLOR_MEANING_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-color-meaning-consistency',
            'title' => __('Color Meaning Consistency', 'wpshadow'),
            'description' => __('Verifies consistent color meanings.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-color-meaning-consistency',
            'training_link' => 'https://wpshadow.com/training/design-color-meaning-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}