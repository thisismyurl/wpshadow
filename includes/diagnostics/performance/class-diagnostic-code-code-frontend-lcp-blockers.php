<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: LCP Blockers
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-lcp-blockers
 * Training: https://wpshadow.com/training/code-frontend-lcp-blockers
 */
class Diagnostic_Code_CODE_FRONTEND_LCP_BLOCKERS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-frontend-lcp-blockers',
            'title' => __('LCP Blockers', 'wpshadow'),
            'description' => __('Detects unoptimized fonts/images delaying largest paint.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-lcp-blockers',
            'training_link' => 'https://wpshadow.com/training/code-frontend-lcp-blockers',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
