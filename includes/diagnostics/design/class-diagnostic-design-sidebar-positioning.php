<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Sidebar Positioning Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-sidebar-positioning
 * Training: https://wpshadow.com/training/design-sidebar-positioning
 */
class Diagnostic_Design_SIDEBAR_POSITIONING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-sidebar-positioning',
            'title' => __('Sidebar Positioning Consistency', 'wpshadow'),
            'description' => __('Verifies sidebars positioned consistently (left/right).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sidebar-positioning',
            'training_link' => 'https://wpshadow.com/training/design-sidebar-positioning',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}