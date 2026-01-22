<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Long JS Tasks
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-long-tasks
 * Training: https://wpshadow.com/training/code-frontend-long-tasks
 */
class Diagnostic_Code_CODE_FRONTEND_LONG_TASKS {
    public static function check() {
        return [
            'id' => 'code-frontend-long-tasks',
            'title' => __('Long JS Tasks', 'wpshadow'),
            'description' => __('Flags unthrottled event handlers or heavy computations on main thread.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-long-tasks',
            'training_link' => 'https://wpshadow.com/training/code-frontend-long-tasks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

