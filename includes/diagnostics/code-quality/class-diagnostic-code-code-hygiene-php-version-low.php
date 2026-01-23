<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PHP Version Too Low
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-php-version-low
 * Training: https://wpshadow.com/training/code-hygiene-php-version-low
 */
class Diagnostic_Code_CODE_HYGIENE_PHP_VERSION_LOW extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-php-version-low',
            'title' => __('PHP Version Too Low', 'wpshadow'),
            'description' => __('Detects plugins requiring PHP < 7.4 (EOL risk).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-php-version-low',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-php-version-low',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}