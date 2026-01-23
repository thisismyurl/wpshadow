<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Inconsistent Text Domain
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-text-domain
 * Training: https://wpshadow.com/training/code-standards-text-domain
 */
class Diagnostic_Code_CODE_STANDARDS_TEXT_DOMAIN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-text-domain',
            'title' => __('Inconsistent Text Domain', 'wpshadow'),
            'description' => __('Flags __() calls with wrong/missing text domain.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-text-domain',
            'training_link' => 'https://wpshadow.com/training/code-standards-text-domain',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}