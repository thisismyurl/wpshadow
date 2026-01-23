<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: AJAX No Security Check
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-ajax-no-security
 * Training: https://wpshadow.com/training/code-ajax-no-security
 */
class Diagnostic_Code_CODE_AJAX_NO_SECURITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-ajax-no-security',
            'title' => __('AJAX No Security Check', 'wpshadow'),
            'description' => __('Flags AJAX handlers lacking nonce/capability verification.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-ajax-no-security',
            'training_link' => 'https://wpshadow.com/training/code-ajax-no-security',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}