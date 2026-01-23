<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network RTL Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-rtl-coverage
 * Training: https://wpshadow.com/training/design-network-rtl-coverage
 */
class Diagnostic_Design_DESIGN_NETWORK_RTL_COVERAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-rtl-coverage',
            'title' => __('Network RTL Coverage', 'wpshadow'),
            'description' => __('Checks RTL assets and styles across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-rtl-coverage',
            'training_link' => 'https://wpshadow.com/training/design-network-rtl-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}