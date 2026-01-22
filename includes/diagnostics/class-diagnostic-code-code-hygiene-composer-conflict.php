<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Composer Autoload Conflicts
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-composer-conflict
 * Training: https://wpshadow.com/training/code-hygiene-composer-conflict
 */
class Diagnostic_Code_CODE_HYGIENE_COMPOSER_CONFLICT {
    public static function check() {
        return [
            'id' => 'code-hygiene-composer-conflict',
            'title' => __('Composer Autoload Conflicts', 'wpshadow'),
            'description' => __('Flags conflicting composer/vendor autoload versions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-composer-conflict',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-composer-conflict',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

