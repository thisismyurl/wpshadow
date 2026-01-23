<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Saturation Appropriateness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-saturation-appropriateness
 * Training: https://wpshadow.com/training/design-saturation-appropriateness
 */
class Diagnostic_Design_SATURATION_APPROPRIATENESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-saturation-appropriateness',
            'title' => __('Saturation Appropriateness', 'wpshadow'),
            'description' => __('Checks colors not oversaturated.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-saturation-appropriateness',
            'training_link' => 'https://wpshadow.com/training/design-saturation-appropriateness',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}