<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multi-Site Customizer Sync
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-multisite-customizer-sync
 * Training: https://wpshadow.com/training/design-multisite-customizer-sync
 */
class Diagnostic_Design_MULTISITE_CUSTOMIZER_SYNC extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-multisite-customizer-sync',
            'title' => __('Multi-Site Customizer Sync', 'wpshadow'),
            'description' => __('Validates customizer settings sync across network.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-multisite-customizer-sync',
            'training_link' => 'https://wpshadow.com/training/design-multisite-customizer-sync',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}