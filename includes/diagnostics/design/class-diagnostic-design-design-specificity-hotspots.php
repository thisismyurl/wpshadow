<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Specificity Hotspots
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-specificity-hotspots
 * Training: https://wpshadow.com/training/design-specificity-hotspots
 */
class Diagnostic_Design_DESIGN_SPECIFICITY_HOTSPOTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-specificity-hotspots',
            'title' => __('Specificity Hotspots', 'wpshadow'),
            'description' => __('Maps !important and high-specificity clusters for cleanup.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-specificity-hotspots',
            'training_link' => 'https://wpshadow.com/training/design-specificity-hotspots',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}