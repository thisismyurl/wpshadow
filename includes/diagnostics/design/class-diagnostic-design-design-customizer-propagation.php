<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Customizer Propagation
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-customizer-propagation
 * Training: https://wpshadow.com/training/design-customizer-propagation
 */
class Diagnostic_Design_DESIGN_CUSTOMIZER_PROPAGATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-customizer-propagation',
            'title' => __('Customizer Propagation', 'wpshadow'),
            'description' => __('Checks customizer settings reflect on the front-end.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-customizer-propagation',
            'training_link' => 'https://wpshadow.com/training/design-customizer-propagation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
