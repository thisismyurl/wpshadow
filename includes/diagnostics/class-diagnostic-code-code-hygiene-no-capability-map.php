<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Settings No Capability Map
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-no-capability-map
 * Training: https://wpshadow.com/training/code-hygiene-no-capability-map
 */
class Diagnostic_Code_CODE_HYGIENE_NO_CAPABILITY_MAP {
    public static function check() {
        return [
            'id' => 'code-hygiene-no-capability-map',
            'title' => __('Settings No Capability Map', 'wpshadow'),
            'description' => __('Flags options pages without cap checks for role/multisite.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-no-capability-map',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-no-capability-map',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

