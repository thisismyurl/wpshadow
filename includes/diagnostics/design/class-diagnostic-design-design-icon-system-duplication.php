<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Icon System Duplication
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-icon-system-duplication
 * Training: https://wpshadow.com/training/design-icon-system-duplication
 */
class Diagnostic_Design_DESIGN_ICON_SYSTEM_DUPLICATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-icon-system-duplication',
            'title' => __('Icon System Duplication', 'wpshadow'),
            'description' => __('Detects multiple icon sets or SVG sources in use.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-icon-system-duplication',
            'training_link' => 'https://wpshadow.com/training/design-icon-system-duplication',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}