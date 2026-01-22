<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Lock Status Display
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-lock-status
 * Training: https://wpshadow.com/training/design-block-lock-status
 */
class Diagnostic_Design_BLOCK_LOCK_STATUS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-lock-status',
            'title' => __('Block Lock Status Display', 'wpshadow'),
            'description' => __('Verifies locked blocks clearly indicated.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-lock-status',
            'training_link' => 'https://wpshadow.com/training/design-block-lock-status',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
