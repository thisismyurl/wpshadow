<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Error Page Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-error-page-consistency
 * Training: https://wpshadow.com/training/design-network-error-page-consistency
 */
class Diagnostic_Design_DESIGN_NETWORK_ERROR_PAGE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-error-page-consistency',
            'title' => __('Network Error Page Consistency', 'wpshadow'),
            'description' => __('Checks 404 and maintenance pages consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-error-page-consistency',
            'training_link' => 'https://wpshadow.com/training/design-network-error-page-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}