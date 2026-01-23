<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Long Selector Chains
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-long-selector-chains
 * Training: https://wpshadow.com/training/design-long-selector-chains
 */
class Diagnostic_Design_DESIGN_LONG_SELECTOR_CHAINS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-long-selector-chains',
            'title' => __('Long Selector Chains', 'wpshadow'),
            'description' => __('Flags selectors with too many combinators.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-long-selector-chains',
            'training_link' => 'https://wpshadow.com/training/design-long-selector-chains',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}