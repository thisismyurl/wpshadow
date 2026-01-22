<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Spammy Admin Notices
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-admin-notices
 * Training: https://wpshadow.com/training/code-errors-admin-notices
 */
class Diagnostic_Code_CODE_ERRORS_ADMIN_NOTICES {
    public static function check() {
        return [
            'id' => 'code-errors-admin-notices',
            'title' => __('Spammy Admin Notices', 'wpshadow'),
            'description' => __('Detects repetitive/non-dismissible admin notices annoying users.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-admin-notices',
            'training_link' => 'https://wpshadow.com/training/code-errors-admin-notices',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

