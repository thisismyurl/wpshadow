<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused Media Queries
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-css-unused-media-queries
 * Training: https://wpshadow.com/training/design-css-unused-media-queries
 */
class Diagnostic_Design_DESIGN_CSS_UNUSED_MEDIA_QUERIES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-unused-media-queries',
            'title' => __('Unused Media Queries', 'wpshadow'),
            'description' => __('Detects media queries that never trigger due to missing matching styles or conditions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-unused-media-queries',
            'training_link' => 'https://wpshadow.com/training/design-css-unused-media-queries',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
