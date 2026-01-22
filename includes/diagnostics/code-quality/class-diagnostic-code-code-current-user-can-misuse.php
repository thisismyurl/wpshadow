<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: current_user_can Misuse
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-current-user-can-misuse
 * Training: https://wpshadow.com/training/code-current-user-can-misuse
 */
class Diagnostic_Code_CODE_CURRENT_USER_CAN_MISUSE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-current-user-can-misuse',
            'title' => __('current_user_can Misuse', 'wpshadow'),
            'description' => __('Detects current_user_can called with wrong capability format.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-current-user-can-misuse',
            'training_link' => 'https://wpshadow.com/training/code-current-user-can-misuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
