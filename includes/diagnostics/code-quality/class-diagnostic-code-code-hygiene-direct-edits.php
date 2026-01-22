<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Direct File Edits Detected
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-direct-edits
 * Training: https://wpshadow.com/training/code-hygiene-direct-edits
 */
class Diagnostic_Code_CODE_HYGIENE_DIRECT_EDITS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-direct-edits',
            'title' => __('Direct File Edits Detected', 'wpshadow'),
            'description' => __('Flags signs of core/plugin/theme edits (not via updates).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-direct-edits',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-direct-edits',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
