<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Media Query Count
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-media-query-count
 * Training: https://wpshadow.com/training/design-debt-media-query-count
 */
class Diagnostic_Design_DEBT_MEDIA_QUERY_COUNT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-media-query-count',
            'title' => __('Media Query Count', 'wpshadow'),
            'description' => __('Counts media queries (should be aligned to breakpoints).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-media-query-count',
            'training_link' => 'https://wpshadow.com/training/design-debt-media-query-count',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}