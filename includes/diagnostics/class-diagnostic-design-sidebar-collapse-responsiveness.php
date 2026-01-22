<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sidebar Collapse Responsiveness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-sidebar-collapse-responsiveness
 * Training: https://wpshadow.com/training/design-sidebar-collapse-responsiveness
 */
class Diagnostic_Design_SIDEBAR_COLLAPSE_RESPONSIVENESS {
    public static function check() {
        return [
            'id' => 'design-sidebar-collapse-responsiveness',
            'title' => __('Sidebar Collapse Responsiveness', 'wpshadow'),
            'description' => __('Verifies sidebars collapse to hamburger on mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sidebar-collapse-responsiveness',
            'training_link' => 'https://wpshadow.com/training/design-sidebar-collapse-responsiveness',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
