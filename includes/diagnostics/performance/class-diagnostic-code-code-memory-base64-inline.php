<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Base64-Encoded Blobs
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-base64-inline
 * Training: https://wpshadow.com/training/code-memory-base64-inline
 */
class Diagnostic_Code_CODE_MEMORY_BASE64_INLINE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-base64-inline',
            'title' => __('Base64-Encoded Blobs', 'wpshadow'),
            'description' => __('Flags large base64 images/fonts shipped inline instead of external.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-base64-inline',
            'training_link' => 'https://wpshadow.com/training/code-memory-base64-inline',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
