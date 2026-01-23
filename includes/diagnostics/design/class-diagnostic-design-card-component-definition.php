<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Card Component Definition
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-card-component-definition
 * Training: https://wpshadow.com/training/design-card-component-definition
 */
class Diagnostic_Design_CARD_COMPONENT_DEFINITION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-card-component-definition',
            'title' => __('Card Component Definition', 'wpshadow'),
            'description' => __('Verifies cards follow consistent design.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-card-component-definition',
            'training_link' => 'https://wpshadow.com/training/design-card-component-definition',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}