<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Asset Over-Enqueue
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-asset-bloat
 * Training: https://wpshadow.com/training/code-perf-asset-bloat
 */
class Diagnostic_Code_CODE_PERF_ASSET_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-asset-bloat',
            'title' => __('Asset Over-Enqueue', 'wpshadow'),
            'description' => __('Flags CSS/JS enqueued on all pages vs conditional loading.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-asset-bloat',
            'training_link' => 'https://wpshadow.com/training/code-perf-asset-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}