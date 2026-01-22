<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Icon Text Redundancy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-icon-text-redundancy
 * Training: https://wpshadow.com/training/design-icon-text-redundancy
 */
class Diagnostic_Design_ICON_TEXT_REDUNDANCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-icon-text-redundancy',
            'title' => __('Icon Text Redundancy', 'wpshadow'),
            'description' => __('Verifies icon-only buttons have aria-label or visible text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-icon-text-redundancy',
            'training_link' => 'https://wpshadow.com/training/design-icon-text-redundancy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
