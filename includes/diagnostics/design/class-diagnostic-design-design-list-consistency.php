<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: List Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-list-consistency
 * Training: https://wpshadow.com/training/design-list-consistency
 */
class Diagnostic_Design_DESIGN_LIST_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-list-consistency',
            'title' => __('List Consistency', 'wpshadow'),
            'description' => __('Checks UL/OL bullets, indents, and spacing consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-list-consistency',
            'training_link' => 'https://wpshadow.com/training/design-list-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}