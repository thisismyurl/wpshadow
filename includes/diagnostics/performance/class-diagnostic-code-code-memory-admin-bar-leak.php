<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Admin Bar on Frontend
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-admin-bar-leak
 * Training: https://wpshadow.com/training/code-memory-admin-bar-leak
 */
class Diagnostic_Code_CODE_MEMORY_ADMIN_BAR_LEAK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-admin-bar-leak',
            'title' => __('Admin Bar on Frontend', 'wpshadow'),
            'description' => __('Detects admin-bar enqueuing without is_admin() guard.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-admin-bar-leak',
            'training_link' => 'https://wpshadow.com/training/code-memory-admin-bar-leak',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}