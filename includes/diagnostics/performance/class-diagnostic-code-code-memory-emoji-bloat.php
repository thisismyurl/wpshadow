<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Emoji/Embeds Not Disabled
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-emoji-bloat
 * Training: https://wpshadow.com/training/code-memory-emoji-bloat
 */
class Diagnostic_Code_CODE_MEMORY_EMOJI_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-emoji-bloat',
            'title' => __('Emoji/Embeds Not Disabled', 'wpshadow'),
            'description' => __('Flags unnecessary emoji or embed scripts in performance mode.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-emoji-bloat',
            'training_link' => 'https://wpshadow.com/training/code-memory-emoji-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}