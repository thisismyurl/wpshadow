<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Specificity Management
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-specificity-management
 * Training: https://wpshadow.com/training/design-css-specificity-management
 */
class Diagnostic_Design_CSS_SPECIFICITY_MANAGEMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-specificity-management',
            'title' => __('CSS Specificity Management', 'wpshadow'),
            'description' => __('Confirms CSS specificity managed.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-specificity-management',
            'training_link' => 'https://wpshadow.com/training/design-css-specificity-management',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}