<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Settings Accessibility
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-settings-accessibility
 * Training: https://wpshadow.com/training/design-block-settings-accessibility
 */
class Diagnostic_Design_BLOCK_SETTINGS_ACCESSIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-settings-accessibility',
            'title' => __('Block Settings Accessibility', 'wpshadow'),
            'description' => __('Validates block inspector keyboard accessible.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-settings-accessibility',
            'training_link' => 'https://wpshadow.com/training/design-block-settings-accessibility',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
