<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Paint Operations Count
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-paint-operations
 * Training: https://wpshadow.com/training/design-debt-paint-operations
 */
class Diagnostic_Design_DEBT_PAINT_OPERATIONS {
    public static function check() {
        return [
            'id' => 'design-debt-paint-operations',
            'title' => __('Paint Operations Count', 'wpshadow'),
            'description' => __('Counts paint operations (indicator of complexity).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-paint-operations',
            'training_link' => 'https://wpshadow.com/training/design-debt-paint-operations',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
