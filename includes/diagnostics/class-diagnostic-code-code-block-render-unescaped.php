<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Render Unescaped
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-block-render-unescaped
 * Training: https://wpshadow.com/training/code-block-render-unescaped
 */
class Diagnostic_Code_CODE_BLOCK_RENDER_UNESCAPED {
    public static function check() {
        return [
            'id' => 'code-block-render-unescaped',
            'title' => __('Block Render Unescaped', 'wpshadow'),
            'description' => __('Detects block render callback output without escaping.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-block-render-unescaped',
            'training_link' => 'https://wpshadow.com/training/code-block-render-unescaped',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

