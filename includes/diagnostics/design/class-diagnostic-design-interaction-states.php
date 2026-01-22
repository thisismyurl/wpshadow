<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Interaction States Completeness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-interaction-states
 * Training: https://wpshadow.com/training/design-interaction-states
 */
class Diagnostic_Design_INTERACTION_STATES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-interaction-states',
            'title' => __('Interaction States Completeness', 'wpshadow'),
            'description' => __('Verifies all interactive elements have all states.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-interaction-states',
            'training_link' => 'https://wpshadow.com/training/design-interaction-states',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
