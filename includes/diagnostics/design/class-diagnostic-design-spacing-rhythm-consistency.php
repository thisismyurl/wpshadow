<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Spacing & Rhythm Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-spacing-rhythm-consistency
 * Training: https://wpshadow.com/training/design-spacing-rhythm-consistency
 */
class Diagnostic_Design_SPACING_RHYTHM_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-spacing-rhythm-consistency',
            'title' => __('Spacing & Rhythm Consistency', 'wpshadow'),
            'description' => __('Verifies 8px or 4px grid system used consistently throughout design (margins, padding).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-spacing-rhythm-consistency',
            'training_link' => 'https://wpshadow.com/training/design-spacing-rhythm-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}