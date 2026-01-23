<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Link Color Distinction
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-link-color-distinction
 * Training: https://wpshadow.com/training/design-link-color-distinction
 */
class Diagnostic_Design_LINK_COLOR_DISTINCTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-link-color-distinction',
            'title' => __('Link Color Distinction', 'wpshadow'),
            'description' => __('Validates link colors visually distinct.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-link-color-distinction',
            'training_link' => 'https://wpshadow.com/training/design-link-color-distinction',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}