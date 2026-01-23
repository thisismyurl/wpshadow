<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS File Size Trend
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-css-size-growth
 * Training: https://wpshadow.com/training/design-debt-css-size-growth
 */
class Diagnostic_Design_DEBT_CSS_SIZE_GROWTH extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-css-size-growth',
            'title' => __('CSS File Size Trend', 'wpshadow'),
            'description' => __('Tracks CSS bundle size over time (debt indicator).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-css-size-growth',
            'training_link' => 'https://wpshadow.com/training/design-debt-css-size-growth',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}