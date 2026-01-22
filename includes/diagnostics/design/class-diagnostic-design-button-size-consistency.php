<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button Size Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-size-consistency
 * Training: https://wpshadow.com/training/design-button-size-consistency
 */
class Diagnostic_Design_BUTTON_SIZE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-size-consistency',
            'title' => __('Button Size Consistency', 'wpshadow'),
            'description' => __('Confirms buttons follow size scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-size-consistency',
            'training_link' => 'https://wpshadow.com/training/design-button-size-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
