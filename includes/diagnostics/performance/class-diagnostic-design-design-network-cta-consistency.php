<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network CTA Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-cta-consistency
 * Training: https://wpshadow.com/training/design-network-cta-consistency
 */
class Diagnostic_Design_DESIGN_NETWORK_CTA_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-cta-consistency',
            'title' => __('Network CTA Consistency', 'wpshadow'),
            'description' => __('Checks CTA styles consistency network-wide.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-cta-consistency',
            'training_link' => 'https://wpshadow.com/training/design-network-cta-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
