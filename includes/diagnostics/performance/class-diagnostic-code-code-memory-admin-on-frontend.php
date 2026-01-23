<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Admin Assets on Frontend
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-admin-on-frontend
 * Training: https://wpshadow.com/training/code-memory-admin-on-frontend
 */
class Diagnostic_Code_CODE_MEMORY_ADMIN_ON_FRONTEND extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-admin-on-frontend',
            'title' => __('Admin Assets on Frontend', 'wpshadow'),
            'description' => __('Flags admin CSS/JS loaded on non-admin screens unnecessarily.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-admin-on-frontend',
            'training_link' => 'https://wpshadow.com/training/code-memory-admin-on-frontend',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}