<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Figma-to-Code Sync Status
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-figma-sync
 * Training: https://wpshadow.com/training/design-system-figma-sync
 */
class Diagnostic_Design_SYSTEM_FIGMA_SYNC {
    public static function check() {
        return [
            'id' => 'design-system-figma-sync',
            'title' => __('Figma-to-Code Sync Status', 'wpshadow'),
            'description' => __('Checks if code design tokens sync with Figma design tokens.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-figma-sync',
            'training_link' => 'https://wpshadow.com/training/design-system-figma-sync',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
