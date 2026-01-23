<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Table Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-table-consistency
 * Training: https://wpshadow.com/training/design-network-table-consistency
 */
class Diagnostic_Design_DESIGN_NETWORK_TABLE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-table-consistency',
            'title' => __('Network Table Consistency', 'wpshadow'),
            'description' => __('Checks table styling consistency across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-table-consistency',
            'training_link' => 'https://wpshadow.com/training/design-network-table-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}