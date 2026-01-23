<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button System Definition
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-system-definition
 * Training: https://wpshadow.com/training/design-button-system-definition
 */
class Diagnostic_Design_BUTTON_SYSTEM_DEFINITION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-system-definition',
            'title' => __('Button System Definition', 'wpshadow'),
            'description' => __('Verifies button system includes variants.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-system-definition',
            'training_link' => 'https://wpshadow.com/training/design-button-system-definition',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}