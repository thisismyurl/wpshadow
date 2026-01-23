<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Z-Index Governance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-zindex-governance
 * Training: https://wpshadow.com/training/design-zindex-governance
 */
class Diagnostic_Design_DESIGN_ZINDEX_GOVERNANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-zindex-governance',
            'title' => __('Z-Index Governance', 'wpshadow'),
            'description' => __('Inventories z-index usage and flags conflicts or off-scale values.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-zindex-governance',
            'training_link' => 'https://wpshadow.com/training/design-zindex-governance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}