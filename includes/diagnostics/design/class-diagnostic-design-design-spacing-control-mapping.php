<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Spacing Control Mapping
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-spacing-control-mapping
 * Training: https://wpshadow.com/training/design-spacing-control-mapping
 */
class Diagnostic_Design_DESIGN_SPACING_CONTROL_MAPPING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-spacing-control-mapping',
            'title' => __('Spacing Control Mapping', 'wpshadow'),
            'description' => __('Checks spacing controls map to the spacing scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-spacing-control-mapping',
            'training_link' => 'https://wpshadow.com/training/design-spacing-control-mapping',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}