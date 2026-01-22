<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Flexbox vs Grid Strategy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-flexbox-vs-grid-strategy
 * Training: https://wpshadow.com/training/design-flexbox-vs-grid-strategy
 */
class Diagnostic_Design_FLEXBOX_VS_GRID_STRATEGY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-flexbox-vs-grid-strategy',
            'title' => __('Flexbox vs Grid Strategy', 'wpshadow'),
            'description' => __('Validates appropriate CSS layout method (flexbox vs grid).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-flexbox-vs-grid-strategy',
            'training_link' => 'https://wpshadow.com/training/design-flexbox-vs-grid-strategy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
