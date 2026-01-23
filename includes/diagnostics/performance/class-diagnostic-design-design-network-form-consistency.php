<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Form Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-form-consistency
 * Training: https://wpshadow.com/training/design-network-form-consistency
 */
class Diagnostic_Design_DESIGN_NETWORK_FORM_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-form-consistency',
            'title' => __('Network Form Consistency', 'wpshadow'),
            'description' => __('Checks form styling consistency across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-form-consistency',
            'training_link' => 'https://wpshadow.com/training/design-network-form-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}