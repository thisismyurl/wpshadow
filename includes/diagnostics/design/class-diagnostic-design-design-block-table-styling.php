<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Table Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-table-styling
 * Training: https://wpshadow.com/training/design-block-table-styling
 */
class Diagnostic_Design_DESIGN_BLOCK_TABLE_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-table-styling',
            'title' => __('Block Table Styling', 'wpshadow'),
            'description' => __('Ensures table block headers, borders, and striping are consistent.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-table-styling',
            'training_link' => 'https://wpshadow.com/training/design-block-table-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
