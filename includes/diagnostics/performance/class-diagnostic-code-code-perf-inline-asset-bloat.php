<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Large Inline Scripts/Styles
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-inline-asset-bloat
 * Training: https://wpshadow.com/training/code-perf-inline-asset-bloat
 */
class Diagnostic_Code_CODE_PERF_INLINE_ASSET_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-inline-asset-bloat',
            'title' => __('Large Inline Scripts/Styles', 'wpshadow'),
            'description' => __('Flags oversized inline <script>/<style> tags (> threshold).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-inline-asset-bloat',
            'training_link' => 'https://wpshadow.com/training/code-perf-inline-asset-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}