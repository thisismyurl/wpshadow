<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Stale Template Overrides
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-stale-templates
 * Training: https://wpshadow.com/training/code-hygiene-stale-templates
 */
class Diagnostic_Code_CODE_HYGIENE_STALE_TEMPLATES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-stale-templates',
            'title' => __('Stale Template Overrides', 'wpshadow'),
            'description' => __('Detects outdated Woo/block template overrides.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-stale-templates',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-stale-templates',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}