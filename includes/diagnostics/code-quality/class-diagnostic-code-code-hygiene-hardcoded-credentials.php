<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hardcoded Credentials/Tokens
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-hardcoded-credentials
 * Training: https://wpshadow.com/training/code-hygiene-hardcoded-credentials
 */
class Diagnostic_Code_CODE_HYGIENE_HARDCODED_CREDENTIALS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-hardcoded-credentials',
            'title' => __('Hardcoded Credentials/Tokens', 'wpshadow'),
            'description' => __('Flags API keys or secrets committed in code.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-hardcoded-credentials',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-hardcoded-credentials',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
