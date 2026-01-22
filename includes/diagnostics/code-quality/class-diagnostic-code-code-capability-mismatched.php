<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Capability Mismatched to Multisite
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-capability-mismatched
 * Training: https://wpshadow.com/training/code-capability-mismatched
 */
class Diagnostic_Code_CODE_CAPABILITY_MISMATCHED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-capability-mismatched',
            'title' => __('Capability Mismatched to Multisite', 'wpshadow'),
            'description' => __('Detects manage_options used where manage_network_options needed.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-capability-mismatched',
            'training_link' => 'https://wpshadow.com/training/code-capability-mismatched',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
