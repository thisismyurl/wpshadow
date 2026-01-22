<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Capability Enforcement
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-capability-checks
 * Training: https://wpshadow.com/training/code-security-capability-checks
 */
class Diagnostic_Code_CODE_SECURITY_CAPABILITY_CHECKS {
    public static function check() {
        return [
            'id' => 'code-security-capability-checks',
            'title' => __('Capability Enforcement', 'wpshadow'),
            'description' => __('Flags actions/filters missing capability checks before modifications.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-capability-checks',
            'training_link' => 'https://wpshadow.com/training/code-security-capability-checks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

