<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Wrong Text Domain
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-text-domain-wrong
 * Training: https://wpshadow.com/training/code-text-domain-wrong
 */
class Diagnostic_Code_CODE_TEXT_DOMAIN_WRONG {
    public static function check() {
        return [
            'id' => 'code-text-domain-wrong',
            'title' => __('Wrong Text Domain', 'wpshadow'),
            'description' => __('Flags translation functions with incorrect text domain.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-text-domain-wrong',
            'training_link' => 'https://wpshadow.com/training/code-text-domain-wrong',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

