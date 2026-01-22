<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Badge and Chip Overflow
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-badge-chip-overflow
 * Training: https://wpshadow.com/training/design-badge-chip-overflow
 */
class Diagnostic_Design_DESIGN_BADGE_CHIP_OVERFLOW extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-badge-chip-overflow',
            'title' => __('Badge and Chip Overflow', 'wpshadow'),
            'description' => __('Checks chips or pills under long localized text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-badge-chip-overflow',
            'training_link' => 'https://wpshadow.com/training/design-badge-chip-overflow',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
